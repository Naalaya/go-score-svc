# Go Score Service

Backend API for the 2024 High School Graduation Exam score lookup and statistics system.

## Tech Stack

-   **Framework**: Laravel 12
-   **Database**: MySQL
-   **PHP Version**: >= 8.2

## Features

✅ Import data from CSV (over 1 million records)  
✅ API for score lookup by student registration number  
✅ API for score statistics with 4 levels (Excellent, Good, Average, Weak)  
✅ API for top 10 students in Group A  
✅ OOP pattern for subject management  
✅ Batch processing for large data imports

## Installation

### 1. Clone project and install dependencies

```bash
cd go-score-svc
composer install
```

### 2. Environment Configuration

Copy `.env.example` to `.env` and configure database:

```bash
cp .env.example .env
```

Edit database information in `.env` file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=score_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Create Database

Create `score_db` database in MySQL:

```sql
CREATE DATABASE score_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Migrations

```bash
make db
```

<!-- ### 6. Import data from CSV

There are 2 ways to import data:

**Method 1: Using Artisan Command**

```bash
php artisan scores:import
```

**Method 2: Using Seeder**

```bash
php artisan db:seed --class=ScoreSeeder
```

⚠️ **Note**: CSV file has been copied to `database/seeders/diem_thi_thpt_2024.csv` -->

### 6. Start Server

```bash
php artisan serve
```

Server will run at: `http://localhost:8000`

## API Endpoints

### 1. Score lookup by student registration number

**POST** `/api/scores/search`

Request Body:

```json
{
    "sbd": "01000001",
    "year": 2024,
    "include_statistics": true,
    "include_metadata": true
}
```

**Parameters:**

-   `sbd` (required): Student registration number (8-10 digits)
-   `year` (optional): Exam year (2020-2025)
-   `include_statistics` (optional): Include overall statistics in response
-   `include_metadata` (optional): Include search metadata

Response:

```json
{
    "success": true,
    "data": {
        "id": 1,
        "sbd": "01000001",
        "toan": 8.4,
        "ngu_van": 6.75,
        "ngoai_ngu": 8.0,
        "vat_li": 6.0,
        "hoa_hoc": 5.25,
        "sinh_hoc": 5.0,
        "lich_su": null,
        "dia_li": null,
        "gdcd": null,
        "ma_ngoai_ngu": "N1",
        "grade_levels": {
            "toan": {
                "score": 8.4,
                "level": "Giỏi",
                "display_name": "Toán"
            },
            ...
        },
        "total_group_a": 19.65
    }
}
```

### 2. Subject score statistics

**GET** `/api/scores/statistics`

**Query Parameters:**

-   `group_code` (optional): Filter by exam group (A, B, C, D)
-   `subject_codes[]` (optional): Filter by specific subjects
-   `include_percentages` (optional): Include percentage calculations
-   `format` (optional): Response format (json, csv)

**Examples:**

-   `/api/scores/statistics?group_code=A`
-   `/api/scores/statistics?include_percentages=true`
-   `/api/scores/statistics?subject_codes[]=toan&subject_codes[]=vat_li`

Response:

```json
{
    "success": true,
    "data": {
        "statistics": [
            {
                "subject_name": "Toán",
                "subject_code": "toan",
                "excellent": 12500,
                "good": 25000,
                "average": 35000,
                "weak": 15000,
                "total": 87500,
                "average_score": 6.25,
                "max_score": 10,
                "min_score": 0,
                "percentages": {
                    "excellent": 14.29,
                    "good": 28.57,
                    "average": 40.0,
                    "weak": 17.14
                }
            },
            ...
        ],
        "summary": {
            "total_students": 1061605,
            "total_subjects": 9,
            "generated_at": "2024-06-27 14:30:00"
        }
    },
    "api_version": "2.0"
    }
}
```

### 3. Top 10 students in Group A

**GET** `/api/scores/top10-group-a`

Response:

```json
{
    "success": true,
    "data": {
        "top_students": [
            {
                "rank": 1,
                "sbd": "01012345",
                "toan": 10.0,
                "vat_li": 9.75,
                "hoa_hoc": 9.5,
                "total_score": 29.25
            },
            ...
        ],
        "group_name": "Khối A",
        "subjects": ["Toán", "Vật lý", "Hóa học"]
    }
}
```

### 4. Get all subjects

**GET** `/api/subjects`

Response:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "toan",
            "display_name": "Toán",
            "group_code": "A",
            "order": 1,
            "is_active": true
        },
        {
            "id": 2,
            "code": "ngu_van",
            "display_name": "Ngữ văn",
            "group_code": null,
            "order": 2,
            "is_active": true
        }
    ]
}
```

## Error Handling

All API endpoints return consistent error responses:

### Validation Error (422)

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "sbd": ["Số báo danh phải gồm 8-10 chữ số"]
    }
}
```

### Not Found (404)

```json
{
    "success": false,
    "message": "Không tìm thấy số báo danh này"
}
```

### Server Error (500)

```json
{
    "success": false,
    "message": "Internal Server Error"
}
```

## Validation Rules

### Search Request

-   `sbd`: Required, 8-10 digits, numeric only
-   `year`: Optional, integer between 2020-2025
-   `include_statistics`: Optional, boolean
-   `include_metadata`: Optional, boolean

### Statistics Request

-   `group_code`: Optional, must be A, B, C, or D
-   `subject_codes[]`: Optional, array of valid subject codes
-   `include_percentages`: Optional, boolean
-   `format`: Optional, json or csv

## OOP Structure

Project uses OOP pattern for subject management:

```
app/Services/Subjects/
├── SubjectService.php       # Subject management service
├── ScoringService.php       # Scoring calculation service
├── Contracts/
│   ├── SubjectServiceInterface.php
│   └── ScoringServiceInterface.php
├── Enums/
│   ├── SubjectCode.php      # Subject code enum
│   └── GradeLevel.php       # Grade level enum
└── Exceptions/              # Custom exceptions
```

## Grade Level System

The API uses a 4-level grading system:

| Level     | Vietnamese | Score Range |
| --------- | ---------- | ----------- |
| excellent | Giỏi       | 8.0 - 10.0  |
| good      | Khá        | 6.0 - 7.9   |
| average   | Trung bình | 4.0 - 5.9   |
| weak      | Yếu        | 0.0 - 3.9   |

## Subject Codes Reference

| Code      | Vietnamese Name | English Name     | Group |
| --------- | --------------- | ---------------- | ----- |
| toan      | Toán            | Mathematics      | A     |
| ngu_van   | Ngữ văn         | Literature       | -     |
| ngoai_ngu | Ngoại ngữ       | Foreign Language | -     |
| vat_li    | Vật lý          | Physics          | A     |
| hoa_hoc   | Hóa học         | Chemistry        | A     |
| sinh_hoc  | Sinh học        | Biology          | B     |
| lich_su   | Lịch sử         | History          | C     |
| dia_li    | Địa lý          | Geography        | C     |
| gdcd      | GDCD            | Civic Education  | D     |

## Exam Groups

| Group | Vietnamese | Subjects                 |
| ----- | ---------- | ------------------------ |
| A     | Khối A     | Toán, Vật lý, Hóa học    |
| B     | Khối B     | Toán, Hóa học, Sinh học  |
| C     | Khối C     | Ngữ văn, Lịch sử, Địa lý |
| D     | Khối D     | Ngữ văn, Toán, Ngoại ngữ |

## Performance

-   Import 1M+ records: ~2-3 minutes (batch size: 1000)
-   Optimized with indexes for score columns
-   Using transactions and batch insert

## Troubleshooting

1. **Memory error when importing CSV**: Increase `memory_limit` in `php.ini`
2. **Timeout during import**: Run command in terminal instead of web browser
3. **CORS errors**: Check configuration in `config/cors.php`

## Testing

```bash
php artisan test
```

## Contributing

1. Fork project
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

**GOOD LUCK!!!**

![Your Code Work](./public/meme.png)
