# Thiết kế Database — An Nhiên AI

PostgreSQL · Laravel 13 · Spatie Permission/Activitylog
Dựa trên đặc tả 22 chức năng (F01–F22) và tài liệu Ý tưởng/Rủi ro y tế-pháp lý (7/2026)

## Quy ước chung

- Khóa chính: `id BIGINT` (Laravel `bigIncrements`).
- Mọi bảng có `created_at`, `updated_at`; bảng có nội dung y tế/nhạy cảm thêm `deleted_at` (soft delete) để phục vụ audit và pháp lý (không xóa cứng dữ liệu y tế).
- Khóa ngoại tới `users.id` trỏ theo vai trò cụ thể (patient/family/caregiver/doctor/admin) — vai trò quản lý qua Spatie Permission (`roles`, `permissions`, `model_has_roles`), không tạo cột `role` cứng trong `users`.
- **Pattern "Xác minh chuyên môn" (F12/F13)** lặp lại ở nhiều bảng: mọi bảng chứa dữ liệu do AI/OCR/người dùng tạo mà ảnh hưởng tới quyết định y tế đều có bộ cột chuẩn:
  `verification_status ENUM('pending','verified','rejected')`, `verified_by BIGINT NULL → users.id`, `verified_at TIMESTAMP NULL`, `rejection_reason TEXT NULL`.
  Đây là hàng rào an toàn bắt buộc theo mục 7 tài liệu rủi ro — không được bỏ qua ở bất kỳ bảng nào chứa thuốc/liều/chỉ định/cảnh báo.
- Tọa độ vị trí lưu `latitude DECIMAL(10,7)`, `longitude DECIMAL(10,7)`.
- Tiền tệ lưu `DECIMAL(12,2)` (VND không có phần thập phân nhưng giữ decimal để mở rộng đa tiền tệ sau này).

---

## 1. Nhóm Auth & Người dùng

### `users`
| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| email | varchar, unique | |
| phone | varchar, unique nullable | |
| password | varchar | |
| email_verified_at | timestamp null | |
| phone_verified_at | timestamp null | |
| status | enum(active,pending,suspended,deactivated) | F16 |
| locale | varchar default 'vi' | |
| avatar_url | varchar null | |
| mfa_enabled | boolean default false | F22 |
| mfa_secret | varchar null, encrypted | F22 |
| last_login_at | timestamp null | |
| created_at, updated_at, deleted_at | | |

Roles/permissions dùng nguyên bảng của **Spatie Permission**: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`. Vai trò gợi ý: `patient`, `family_member`, `caregiver`, `nurse`, `doctor`, `admin`.

### `patient_profiles`
| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK→users, unique | |
| date_of_birth | date | |
| gender | enum(male,female,other) | |
| national_id | varchar null, encrypted | dữ liệu định danh nhạy cảm |
| address | varchar null | |
| city, district | varchar null | |
| blood_type | varchar(5) null | |
| height_cm, weight_kg | decimal null | |
| primary_condition_summary | text null | tóm tắt tình trạng chính, hiển thị nhanh |
| care_level | enum(self_care,assisted,dependent) null | |

### `caregiver_profiles`
| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK→users, unique | |
| caregiver_type | enum(nurse,volunteer,personal_caregiver) | F06 |
| license_number | varchar null | bắt buộc nếu type=nurse |
| license_verified_status | enum(pending,verified,rejected) | F16 — xác minh hành nghề |
| license_verified_by | bigint FK→users null | admin thực hiện |
| license_verified_at | timestamp null | |
| years_experience | smallint null | |
| bio | text null | |
| skills | json null | danh sách kỹ năng |
| hourly_rate | decimal(12,2) null | |
| service_radius_km | smallint null | |
| base_latitude, base_longitude | decimal null | |
| background_check_status | enum(pending,cleared,flagged) | F16 |
| rating_avg | decimal(3,2) default 0 | |
| rating_count | int default 0 | |

### `doctor_profiles`
| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint PK | |
| user_id | bigint FK→users, unique | |
| license_number | varchar | |
| specialty | varchar | |
| hospital_affiliation | varchar null | |
| license_verified_status | enum(pending,verified,rejected) | F16 |
| license_verified_by | bigint FK→users null | |
| license_verified_at | timestamp null | |
| consultation_fee | decimal(12,2) null | F07/F19 |
| can_author_alert_rules | boolean default false | phân quyền F14 |

### `family_links`
Liên kết người nhà ↔ bệnh nhân, xử lý vấn đề "đại diện hợp pháp" (mục 6 tài liệu rủi ro).

| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | bigint PK | |
| patient_id | bigint FK→users | |
| family_user_id | bigint FK→users | |
| relationship_type | varchar | con, vợ/chồng, người giám hộ... |
| permission_level | enum(full,view_only,emergency_only) | |
| consent_document_url | varchar null | bằng chứng ủy quyền |
| consented_at | timestamp null | |
| status | enum(pending,active,revoked) | |
| created_by | bigint FK→users | |

---

## 2. Nhóm Hồ sơ sức khỏe số (F01, F02, F03)

### `medical_conditions` (bệnh nền)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK→patient_profiles.user_id |
| condition_name | varchar |
| icd_code | varchar null |
| diagnosed_date | date null |
| notes | text null |
| status | enum(active,resolved,chronic) |
| source | enum(manual,ocr,ai_draft) |
| verification_status, verified_by, verified_at, rejection_reason | *(chuẩn F12/F13)* |
| created_by | bigint FK→users |

### `allergies`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| allergen | varchar |
| reaction | text null |
| severity | enum(mild,moderate,severe) |
| source | enum(manual,ocr,ai_draft) |
| verification_status, verified_by, verified_at, rejection_reason | |
| created_by | bigint FK→users |

### `medications` (thuốc đang dùng)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| drug_name | varchar |
| dosage | varchar |
| frequency | varchar | vd "2 lần/ngày" |
| route | varchar null | uống, tiêm... |
| start_date | date |
| end_date | date null |
| prescribing_doctor_id | bigint FK→users null |
| instructions | text null |
| source | enum(manual,ocr,ai_draft) |
| verification_status, verified_by, verified_at, rejection_reason | |
| created_by | bigint FK→users |

### `lab_results` (kết quả xét nghiệm)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| test_name | varchar |
| result_value | varchar |
| unit | varchar null |
| reference_range | varchar null |
| test_date | date |
| file_url | varchar null | file gốc/scan |
| source | enum(manual,ocr) |
| verification_status, verified_by, verified_at, rejection_reason | |
| created_by | bigint FK→users |

### `medical_orders` (chỉ định)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| doctor_id | bigint FK→users |
| order_type | enum(medication,test,care_plan,follow_up) |
| description | text |
| start_date | date |
| end_date | date null |
| verification_status, verified_by, verified_at, rejection_reason | |
| created_at, updated_at | |

> `medical_orders` đã xác minh (`verification_status = verified`) là nguồn sinh ra `care_schedules` (mục 3).

### `emergency_contacts`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| name | varchar |
| relationship | varchar |
| phone | varchar |
| priority_order | smallint | thứ tự gọi khi khẩn cấp |

### `document_scans` (F02 — OCR)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| uploaded_by | bigint FK→users |
| file_url | varchar |
| file_type | enum(image,pdf) |
| ocr_status | enum(processing,done,failed) |
| ocr_raw_text | text null |
| ocr_confidence | decimal(5,2) null |
| linked_record_type | varchar null | polymorphic: medication, lab_result... |
| linked_record_id | bigint null | id bản ghi được tạo ra từ scan này |
| review_status | enum(pending,edited,confirmed,rejected) | người dùng tự kiểm tra bản quét (không phải xác minh y tế) |
| reviewed_by | bigint FK→users null |
| reviewed_at | timestamp null |

---

## 3. Nhóm Lịch chăm sóc & Nhật ký triệu chứng (F04, F05, F14)

### `care_schedules`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| source_order_id | bigint FK→medical_orders null |
| type | enum(medication,vital_check,follow_up,task) |
| title | varchar |
| description | text null |
| recurrence_rule | varchar null | RRULE string, vd "FREQ=DAILY;INTERVAL=1" |
| start_date | date |
| end_date | date null |
| status | enum(active,paused,ended) |
| created_by | bigint FK→users |

### `care_schedule_occurrences` (lần cụ thể trong lịch)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| care_schedule_id | bigint FK |
| scheduled_at | datetime |
| status | enum(pending,completed,missed,skipped) |
| completed_at | timestamp null |
| completed_by | bigint FK→users null |
| notes | text null |

### `symptom_logs` (nhật ký triệu chứng)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| logged_by | bigint FK→users |
| log_date | date |
| pain_level | smallint null | 0–10 |
| temperature_celsius | decimal(4,1) null |
| breathing_difficulty | boolean default false |
| appetite | enum(normal,reduced,none) null |
| sleep_quality | enum(good,fair,poor) null |
| mobility | enum(normal,limited,bedridden) null |
| mood | enum(good,neutral,low) null |
| vitals | json null | các chỉ số khác (huyết áp, SpO2, nhịp tim...) |
| notes | text null |

### `alert_rules` (quy tắc cảnh báo đỏ — F14)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| name | varchar |
| metric_type | varchar | vd temperature, pain_level, spo2 |
| condition_operator | enum(>,>=,<,<=,=) |
| threshold_value | decimal(10,2) |
| severity | enum(low,medium,red) |
| scope | enum(global,patient_specific) |
| patient_id | bigint FK null | chỉ set khi scope=patient_specific |
| is_active | boolean default true |
| created_by | bigint FK→users | bác sĩ/điều dưỡng cố vấn |
| updated_at | |

### `alerts` (cảnh báo được kích hoạt)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| alert_rule_id | bigint FK |
| symptom_log_id | bigint FK null | nguồn gây cảnh báo |
| triggered_at | timestamp |
| severity | enum(low,medium,red) |
| message | text |
| status | enum(new,acknowledged,resolved) |
| acknowledged_by | bigint FK→users null |
| acknowledged_at | timestamp null |
| resolved_at | timestamp null |

---

## 4. Nhóm Điều phối dịch vụ chăm sóc (F06, F09, F10, F11)

### `service_requests`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| requested_by | bigint FK→users |
| service_type | varchar | vd chăm sóc theo giờ, điều dưỡng chuyên khoa |
| description | text |
| scope_of_work | text null |
| preferred_start | datetime |
| preferred_end | datetime null |
| location_address | varchar |
| latitude, longitude | decimal null |
| status | enum(open,matched,confirmed,in_progress,completed,cancelled) |

### `caregiver_availability`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| caregiver_id | bigint FK→users |
| day_of_week | tinyint null | 0–6, dùng nếu is_recurring |
| specific_date | date null | dùng nếu không lặp |
| start_time | time |
| end_time | time |
| is_recurring | boolean |

### `bookings`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| service_request_id | bigint FK |
| caregiver_id | bigint FK→users |
| patient_id | bigint FK |
| scheduled_start | datetime |
| scheduled_end | datetime |
| status | enum(pending_confirmation,confirmed,declined,cancelled,completed) |
| confirmed_at | timestamp null |
| price | decimal(12,2) |
| payment_status | enum(unpaid,paid,refunded) |

### `care_sessions` (check-in/out — F10)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| booking_id | bigint FK |
| caregiver_id | bigint FK→users |
| patient_id | bigint FK |
| check_in_at | timestamp null |
| check_in_latitude, check_in_longitude | decimal null |
| check_out_at | timestamp null |
| check_out_latitude, check_out_longitude | decimal null |
| duration_minutes | int null | tính tự động khi check-out |
| status | enum(not_started,in_progress,completed) |

### `care_session_reports` (F11)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| care_session_id | bigint FK, unique |
| patient_condition_summary | text |
| tasks_performed | text |
| medication_given | json null | danh sách thuốc đã cho uống trong buổi |
| observations | text null |
| family_notified | boolean default false |
| doctor_notified | boolean default false |
| created_at | |

---

## 5. Nhóm Tư vấn từ xa & Khẩn cấp (F07, F08)

### `consultations`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| requester_id | bigint FK→users |
| provider_id | bigint FK→users | bác sĩ/điều dưỡng |
| consultation_type | enum(chat,video) |
| status | enum(requested,accepted,in_progress,completed,cancelled) |
| scheduled_at | datetime null |
| started_at | timestamp null |
| ended_at | timestamp null |
| structured_record_snapshot | json null | hồ sơ gửi kèm tại thời điểm yêu cầu |
| summary_notes | text null |
| verification_status, verified_by, verified_at | *(chuẩn F12/F13, áp dụng cho summary_notes)* |

### `consultation_messages`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| consultation_id | bigint FK |
| sender_id | bigint FK→users |
| message_type | enum(text,file,image) |
| content | text |
| sent_at | timestamp |
| read_at | timestamp null |

### `emergency_events` (F08)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| triggered_by | bigint FK→users |
| triggered_at | timestamp |
| latitude, longitude | decimal null |
| contacts_notified | json | danh sách emergency_contacts đã gọi/nhắn kèm kết quả |
| disclaimer_shown | boolean default true | xác nhận đã hiển thị cảnh báo "không phải dịch vụ cấp cứu" |
| status | enum(initiated,contacted,resolved) |
| resolved_at | timestamp null |
| notes | text null |

---

## 6. Nhóm Xác minh chuyên môn (F12, F13)

### `ai_drafts` (bản nháp AI — dùng chung cho F03/F12)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| draft_type | enum(record_explanation,alert_summary,care_plan,weekly_report) |
| source_data | json | dữ liệu đầu vào AI dùng để sinh nội dung |
| ai_generated_text | text |
| model_used | varchar | vd tên model, version |
| generated_at | timestamp |
| edited_text | text null | nội dung sau khi bác sĩ/điều dưỡng chỉnh sửa |
| status | enum(pending,edited,approved,rejected) |
| reviewed_by | bigint FK→users null |
| reviewed_at | timestamp null |

### `verification_signatures` (ký xác minh điện tử — F13)
Bảng polymorphic dùng chung cho mọi loại nội dung cần chữ ký xác minh (ai_drafts, medical_orders, care_session_reports...), tránh lặp cột chữ ký ở từng bảng.

| Cột | Kiểu |
|---|---|
| id | bigint PK |
| verifiable_type | varchar | tên model, vd `AiDraft`, `MedicalOrder` |
| verifiable_id | bigint | id bản ghi tương ứng |
| verified_by | bigint FK→users | bác sĩ/điều dưỡng ký |
| status | enum(pending,verified,rejected) |
| signature_hash | varchar | hash chữ ký điện tử |
| signed_at | timestamp null |
| rejection_reason | text null |
| ip_address | varchar null |
| device_info | varchar null |

> Index `(verifiable_type, verifiable_id)` bắt buộc cho bảng này.

---

## 7. Nhóm Báo cáo (F15, F20)

### `reports`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| patient_id | bigint FK |
| report_type | enum(daily,weekly) |
| period_start | date |
| period_end | date |
| generated_at | timestamp |
| file_url | varchar null | PDF |
| summary_json | json | thay đổi trong kỳ, lần bỏ thuốc, câu hỏi cần trao đổi |
| status | enum(generated,viewed,responded) |

### `report_responses` (F15)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| report_id | bigint FK |
| responded_by | bigint FK→users |
| response_text | text |
| order_adjustments | json null | điều chỉnh chỉ định kèm theo (nếu có) |
| responded_at | timestamp |
| sent_to_user_at | timestamp null |

---

## 8. Nhóm Nền tảng / Quản trị (F16, F17, F18, F21, F22)

### Audit log (F17)
Dùng nguyên bảng `activity_log` của **Spatie Activitylog** (`log_name`, `subject_type`, `subject_id`, `causer_type`, `causer_id`, `properties`, `event`) — không tạo bảng riêng, tránh trùng lặp cơ chế.

### `notifications` (F18)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| user_id | bigint FK→users |
| type | varchar | vd alert_red, schedule_reminder, verification_needed |
| title | varchar |
| body | text |
| channel | enum(push,sms,call,email,in_app) |
| related_type | varchar null | polymorphic tới alerts, care_schedule_occurrences... |
| related_id | bigint null |
| status | enum(queued,sent,delivered,failed,read) |
| sent_at | timestamp null |
| read_at | timestamp null |

### `pilot_programs` (F21)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| name | varchar |
| region | varchar |
| partner_group | varchar null |
| max_participants | int |
| start_date | date |
| end_date | date null |
| status | enum(planned,active,closed) |
| created_by | bigint FK→users |

### `pilot_enrollments`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| pilot_program_id | bigint FK |
| user_id | bigint FK→users |
| enrolled_at | timestamp |
| status | enum(enrolled,active,withdrawn) |

### `security_events` (F22)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| user_id | bigint FK→users null |
| event_type | enum(login_success,login_failed,mfa_challenge,password_reset,suspicious_access,account_locked) |
| ip_address | varchar |
| device_info | varchar null |
| occurred_at | timestamp |
| risk_score | smallint null |

---

## 9. Nhóm Thanh toán (F19)

### `subscription_plans`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| name | varchar |
| code | varchar unique |
| price | decimal(12,2) |
| billing_cycle | enum(monthly,yearly) |
| features | json |
| is_active | boolean default true |

### `subscriptions`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| user_id | bigint FK→users |
| plan_id | bigint FK→subscription_plans |
| status | enum(active,cancelled,expired,past_due) |
| started_at | timestamp |
| current_period_end | timestamp |
| cancelled_at | timestamp null |

### `orders` (đơn thanh toán — polymorphic)
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| user_id | bigint FK→users |
| orderable_type | varchar | `Subscription`, `Booking`, `Consultation` |
| orderable_id | bigint | |
| amount | decimal(12,2) |
| currency | varchar(3) default 'VND' |
| status | enum(pending,paid,failed,refunded,partially_refunded) |
| payment_method | varchar null |
| paid_at | timestamp null |

### `payment_transactions`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| order_id | bigint FK |
| gateway | varchar | vd VNPay, Momo, Stripe |
| gateway_transaction_id | varchar null |
| amount | decimal(12,2) |
| status | enum(initiated,success,failed) |
| raw_response | json null |
| processed_at | timestamp null |

### `invoices`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| order_id | bigint FK |
| invoice_number | varchar unique |
| issued_at | timestamp |
| pdf_url | varchar null |
| tax_amount | decimal(12,2) default 0 |
| total_amount | decimal(12,2) |

### `refunds`
| Cột | Kiểu |
|---|---|
| id | bigint PK |
| order_id | bigint FK |
| amount | decimal(12,2) |
| reason | text |
| status | enum(requested,approved,rejected,processed) |
| requested_by | bigint FK→users |
| processed_by | bigint FK→users null |
| processed_at | timestamp null |

---

## 10. Bảng ánh xạ Chức năng ↔ Bảng dữ liệu

| Chức năng | Bảng chính liên quan |
|---|---|
| F01 Hồ sơ sức khỏe số | `medical_conditions`, `allergies`, `medications`, `lab_results`, `medical_orders`, `emergency_contacts` |
| F02 Quét OCR | `document_scans` |
| F03 AI giải thích hồ sơ | `ai_drafts`, `verification_signatures` |
| F04 Lịch chăm sóc | `care_schedules`, `care_schedule_occurrences` |
| F05 Nhật ký triệu chứng | `symptom_logs`, `alerts` |
| F06 Đặt người chăm sóc | `service_requests`, `caregiver_availability`, `bookings` |
| F07 Tư vấn từ xa | `consultations`, `consultation_messages` |
| F08 Nút khẩn cấp | `emergency_events`, `emergency_contacts` |
| F09 Nhận lịch làm việc | `bookings`, `caregiver_availability` |
| F10 Check-in/out | `care_sessions` |
| F11 Báo cáo chăm sóc | `care_session_reports` |
| F12 Xác minh bản nháp AI | `ai_drafts`, `verification_signatures` |
| F13 Ký xác minh điện tử | `verification_signatures` |
| F14 Quy tắc cảnh báo đỏ | `alert_rules`, `alerts` |
| F15 Tiếp nhận & phản hồi báo cáo | `reports`, `report_responses` |
| F16 Tài khoản & phân quyền | `users`, Spatie `roles`/`permissions`, `*_profiles` |
| F17 Quản lý hồ sơ & audit | Spatie `activity_log` |
| F18 Cảnh báo & thông báo | `notifications` |
| F19 Thanh toán | `subscription_plans`, `subscriptions`, `orders`, `payment_transactions`, `invoices`, `refunds` |
| F20 Báo cáo tuần PDF | `reports` |
| F21 Pilot | `pilot_programs`, `pilot_enrollments` |
| F22 Bảo mật hệ thống | `security_events`, `users.mfa_*` |

---

## 11. Ghi chú thiết kế quan trọng

1. **Không tách "role table" cứng.** Dùng Spatie Permission để một user có thể mang nhiều vai trò (vd vừa là family_member vừa từng là caregiver), tránh phải sửa schema khi có vai trò mới.
2. **Pattern xác minh là bắt buộc, không tùy chọn** — đây là hàng rào an toàn số 1 trong tài liệu rủi ro pháp lý (Luật Khám bệnh, chữa bệnh 15/2023/QH15). Mọi migration tạo bảng chứa dữ liệu y tế phải include bộ cột `verification_status/verified_by/verified_at` hoặc liên kết qua `verification_signatures`.
3. **`verification_signatures` dùng polymorphic** thay vì lặp cột chữ ký ở từng bảng — tiết kiệm schema và cho phép audit tập trung "ai đã ký gì, khi nào" ở một chỗ duy nhất, phục vụ yêu cầu pháp lý "lưu danh tính, thời gian, phiên bản duyệt".
4. **Soft delete bắt buộc** cho các bảng y tế (`medical_conditions`, `allergies`, `medications`, `lab_results`, `medical_orders`, `symptom_logs`, `ai_drafts`) — không được xóa cứng, phục vụ audit trail và Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15.
5. **`document_scans.linked_record_type/id`** là polymorphic trỏ tới bản ghi được tạo ra sau khi OCR + xác minh (vd một `medications` record) — giữ được liên kết "dữ liệu này đến từ ảnh scan nào" để tra soát khi có sự cố.
6. **Index bắt buộc** cho hiệu năng và truy vết:
   - Mọi cột `patient_id` trong các bảng y tế.
   - `(verifiable_type, verifiable_id)` trên `verification_signatures`.
   - `(orderable_type, orderable_id)` trên `orders`.
   - `alerts.status`, `care_schedule_occurrences.status` (dùng lọc dashboard theo trạng thái liên tục).
   - `notifications(user_id, status)`.
7. **Dữ liệu định danh nhạy cảm** (`national_id`, `mfa_secret`) nên mã hóa ở tầng ứng dụng (Laravel encrypted cast) chứ không chỉ dựa vào quyền DB.
8. Đây là **thiết kế logic** — khi lên migration Laravel thực tế, mỗi bảng `*_profiles`, health-record domain nên đặt trong module riêng theo đúng cấu trúc thư mục theo actor-group bạn đã chọn (Patient/Caregiver/Doctor/Platform), mỗi module có migrations + models + policies riêng.
