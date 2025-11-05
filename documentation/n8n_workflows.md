# n8n Workflows Configuration

## Overview

Portal Digitalisasi Materi menggunakan n8n untuk mengotomasi berbagai proses yang terkait dengan manajemen materi pembelajaran digital. Berikut adalah konfigurasi workflow yang diperlukan:

## 1. Webhook Configuration

### Base URL
```
{{ BASE_URL }}/webhooks/n8n
```

### Authentication
- **Token-based**: Bearer token dari environment variable `N8N_API_TOKEN`
- **IP Whitelist**: Hanya dari server Laravel

## 2. Workflow Templates

### W-Generate-Digital

**Trigger**: Webhook dari Laravel (`/webhooks/n8n/material-generate`)

**Steps**:
1. **Webhook**: Menerima data materi
   ```json
   {
     "material_id": "MTR123ABC",
     "action": "generate_digital",
     "payload": {
       "material_data": {...},
       "folder_id": "GoogleDriveFolderId"
     }
   }
   ```

2. **Google Sheets**: Get material info
   - Spreadsheet ID: `{{ env.GOOGLE_SHEETS_CATALOG_MATERI_ID }}`
   - Range: `Sheet1!A:J`
   - Filter by `material_id`

3. **Google Drive**: Create folder (if not exists)
   - Parent folder: `{{ env.GOOGLE_DRIVE_FOLDER_ID }}`
   - Folder name: `{material_id}_{slug}`
   - Create subfolders: `source`, `video`, `audio`, `flashcards`, `reports`, `forms`

4. **Google Drive**: Download source file
   - Download file dari `drive_source_file_link`
   - Extract text content menggunakan Google Docs API

5. **Gemini API**: Generate video script
   - Prompt: Generate video overview script
   - Output: JSON dengan script dan scene breakdown

6. **Text-to-Speech**: Generate audio podcast
   - Service: Google Cloud Text-to-Speech
   - Voice: id-ID-Wavenet-D (male Indonesian)

7. **Gemini API**: Generate flashcards
   - Prompt: Generate educational flashcards
   - Output: JSON dengan Q&A pairs

8. **Gemini API**: Generate SQ3R report
   - Prompt: Generate SQ3R analysis
   - Output: JSON dengan survey, questions, read guide

9. **Google Drive**: Upload generated files
   - Upload video script JSON
   - Upload audio MP3
   - Upload flashcards JSON/HTML
   - Upload SQ3R report JSON/HTML

10. **Google Sheets**: Update digital catalog
    - Spreadsheet ID: `{{ env.GOOGLE_SHEETS_CATALOG_DIGITAL_ID }}`
    - Update row untuk material_id dengan URLs

11. **Webhook**: Callback ke Laravel
    - Endpoint: `{{ BASE_URL }}/api/webhooks/n8n/job-status`
    - Payload: Job completion status

**Error Handling**:
- Retry mechanism: 3 attempts dengan exponential backoff
- Log errors ke `digital_error_log` column
- Update job status ke `failed` dengan error message

### W-Create-Classroom

**Trigger**: Webhook dari Laravel atau chaining dari W-Generate-Digital

**Steps**:
1. **Webhook**: Menerima request pembuatan classroom
   ```json
   {
     "material_id": "MTR123ABC",
     "action": "create_classroom"
   }
   ```

2. **Google Sheets**: Get material + digital content info
   - Join data dari catalog-materi dan catalog-digital

3. **Gemini API**: Generate quiz questions
   - Input: Material content
   - Output: JSON dengan questions dan multiple choice answers

4. **Google Classroom**: Create course (if not exist)
   - Course name: `{subject_name} - {material_title}`
   - Description: Material description
   - Owner: Service account email

5. **Google Forms**: Create quiz
   - Title: `Quiz: {material_title}`
   - Questions dari Gemini API output
   - Enable quiz mode dan auto-grading

6. **Google Classroom**: Create assignment
   - Link ke Google Forms quiz
   - Due date: 7 hari dari release date
   - Points: 100

7. **Google Sheets**: Update classroom catalog
   - Spreadsheet ID: `{{ env.GOOGLE_SHEETS_CATALOG_CLASSROOM_ID }}`
   - Save classroom_url, gform_url, sheetform_responses_url

8. **Gmail**: Send notification ke teacher
   - Template: Email notifikasi classroom created
   - Include links ke classroom dan quiz

**Error Handling**:
- Update `classroom_status` ke `ERROR`
- Log error message untuk troubleshooting

### W-Schedule-Automation

**Trigger**: Cron node (daily 18:00 WIB)

**Steps**:
1. **Cron**: Set trigger ke 18:00 setiap hari

2. **Google Sheets**: Read schedule automation
   - Spreadsheet ID: `{{ env.GOOGLE_SHEETS_SCHEDULE_AUTOMATION_ID }}`
   - Range: `Sheet1!A:Q`

3. **Function Node**: Filter items
   ```javascript
   // Filter untuk besok (tomorrow)
   const tomorrow = new Date();
   tomorrow.setDate(tomorrow.getDate() + 1);
   const tomorrowStr = tomorrow.toISOString().split('T')[0];

   return items.filter(item => {
     return item.json.date_release === tomorrowStr &&
            item.json.announcement_status === 'PENDING';
   });
   ```

4. **Loop untuk setiap schedule**:
   - **Get Material Data**: Ambil data materi dari Google Sheets
   - **Get Template Data**: Load email dan assignment template
   - **Gmail**: Send announcement email
     ```javascript
     // Template variables
     const variables = {
       material_title: item.json.material_title,
       teacher_name: item.json.proctor_email,
       class_name: item.json.classgroup_email,
       assignment_link: item.json.assignment_url,
       due_date: calculateDueDate(item.json.date_release)
     };
     ```
   - **Google Classroom**: Post announcement (if classroom exists)
   - **Google Sheets**: Update status ke `POSTED`
   - **Error Handler**: Log error dan update status ke `FAILED`

5. **Summary Report**: Kirim email summary ke admin
   - Total announcements sent
   - Total assignments created
   - Failed items dengan error details

**Schedule Configuration**:
- **Runtime**: 18:00 WIB (11:00 UTC)
- **Timezone**: Asia/Jakarta
- **Retry**: Failed items akan dicoba lagi 1 jam kemudian

## 3. Environment Variables

### Required Environment Variables

```bash
# n8n Configuration
N8N_WEBHOOK_URL=https://your-n8n-instance.com/webhook
N8N_API_TOKEN=your_secure_api_token

# Google APIs
GOOGLE_SERVICE_ACCOUNT_CREDENTIALS=/path/to/service-account.json
GOOGLE_DRIVE_FOLDER_ID=your_main_folder_id
GOOGLE_SHEETS_CATALOG_MATERI_ID=your_sheet_id
GOOGLE_SHEETS_CATALOG_DIGITAL_ID=your_sheet_id
GOOGLE_SHEETS_CATALOG_CLASSROOM_ID=your_sheet_id
GOOGLE_SHEETS_SCHEDULE_AUTOMATION_ID=your_sheet_id

# Gemini API
GEMINI_API_KEY=your_gemini_api_key

# Laravel Backend
LARAVEL_WEBHOOK_URL=https://your-laravel-app.com/api/webhooks/n8n
LARAVEL_API_TOKEN=your_laravel_api_token
```

## 4. Setup Instructions

### 1. Install n8n Cloud

```bash
# Create n8n account di https://cloud.n8n.io
# Atau setup self-hosted n8n
docker run -it --rm \
  --name n8n \
  -p 5678:5678 \
  -v ~/.n8n:/home/node/.n8n \
  n8nio/n8n
```

### 2. Configure Google Credentials

1. Buat Service Account di Google Cloud Console
2. Enable APIs: Drive, Sheets, Classroom, Gmail, Docs, Text-to-Speech
3. Download service account JSON file
4. Upload ke n8n credentials

### 3. Import Workflows

1. Copy workflow JSON templates
2. Import ke n8n workspace
3. Configure environment variables
4. Test webhook endpoints

### 4. Setup Webhooks

1. Generate webhook URLs di n8n
2. Update Laravel `.env` dengan webhook URLs
3. Test webhook connectivity

## 5. Monitoring & Troubleshooting

### Monitoring

- **n8n Dashboard**: Monitor workflow execution status
- **Error Logs**: Check individual node errors
- **Execution History**: Review workflow runs
- **Performance**: Monitor execution time dan resource usage

### Common Issues

1. **Google API Quota Exceeded**
   - Solution: Monitor API usage dan setup quota alerts

2. **Large File Processing Timeout**
   - Solution: Increase timeout setting atau split into smaller chunks

3. **Authentication Failures**
   - Solution: Refresh service account credentials

4. **Rate Limiting**
   - Solution: Implement proper rate limiting dan retry logic

### Debugging

```javascript
// Add debug node untuk logging
console.log('Processing material:', material_id);
console.log('Step completed:', step_name);
```

## 6. Security Considerations

1. **Webhook Security**
   - Use Bearer token authentication
   - IP whitelisting
   - Request validation

2. **Credential Management**
   - Use n8n credential store
   - Regular rotation of API keys
   - Principle of least privilege

3. **Data Privacy**
   - Encrypt sensitive data
   - Comply with educational data regulations
   - Audit trail untuk data access

## 7. Performance Optimization

1. **Batch Processing**
   - Group multiple materials dalam satu execution
   - Use parallel processing dimana possible

2. **Caching**
   - Cache template data
   - Cache API responses yang tidak sering berubah

3. **Resource Management**
   - Monitor memory usage
   - Optimize file processing
   - Use streaming untuk large files

## 8. Backup & Recovery

1. **Workflow Backup**
   - Export workflow JSON regularly
   - Version control untuk workflow changes

2. **Data Recovery**
   - Google Sheets auto-revision history
   - Backup untuk generated content

3. **Disaster Recovery**
   - Redundant n8n instances
   - Manual override procedures