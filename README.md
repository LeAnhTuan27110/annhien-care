# An Nhien Care

An Nhien Care is a web and mobile platform for supporting at-home patient care. Its core principle is:

> AI assists - doctors and nurses verify - healthcare professionals decide.

The platform should not be positioned as an "AI doctor". AI is used to draft, extract, explain, and summarize information. Any health-related content, including medication, dosage, clinical instructions, alerts, lab results, or care plans, must be verified by an authorized doctor or nurse before it is applied to a patient.

## Product Goal

An Nhien Care acts as a "home care health assistant" that connects:

- Patients and family caregivers.
- Nurses, caregivers, and volunteers.
- Consulting doctors.
- The platform operations team.

The main value is reducing the confusion of at-home care, turning medical records into daily tasks, and creating a continuous information flow between families and healthcare professionals.

## Problems To Solve

- Families often receive prescriptions, lab results, and medical instructions that are hard to understand and difficult to turn into daily care tasks.
- After a patient leaves the hospital, care information is often scattered across paper documents, chat messages, and family memory.
- Families struggle to find suitable caregivers or nurses based on skills, availability, location, and trust level.
- Doctors and nurses have limited visibility into what happens between clinical visits because home-care data is usually unstructured.

## MVP Scope

The MVP should start with a specific patient group, such as elderly patients with chronic conditions or patients recently discharged from hospital.

- Accounts and roles: patient, family member, nurse, doctor, administrator.
- Patient profile: basic information, chronic conditions, allergies, current medications, emergency contacts.
- Document scanning: OCR for images/PDFs, uncertainty flags, and professional verification for health data.
- Controlled AI explanation: AI drafts explanations, then a doctor or nurse verifies them before they are shown to users.
- Medication and care schedule: generated only from verified data.
- Symptom journal: pain, fever, shortness of breath, eating, sleep, mobility, and mood.
- Weekly report: changes, missed medication events, and questions to discuss with healthcare staff.
- Trial booking flow: connect a small group of nurses or partners within one service area.

## Safety Principles

- AI must not diagnose, prescribe, change treatment, or send direct health recommendations before professional verification.
- Every health-related AI output must have a status: `Pending verification`, `Verified`, or `Rejected`.
- The system must store the reviewer, review time, edited content, original content, and applied version.
- Users may only self-confirm administrative information. They must not self-confirm medication, dosage, diagnosis, abnormal indicators, alerts, or care plans.
- The app should include emergency-call or emergency-contact actions and clearly state that it is not an emergency service.
- Role-based access control, strong authentication, encryption, and access logs are required before using real patient data.

## Value And Revenue Model

- Basic profile and medication reminders: free or freemium to build usage habits.
- AI record explanation: premium plan with professional verification included.
- Periodic health reports: monthly personal or family plan.
- Caregiver/nurse booking: per-order fee or transaction commission.
- Doctor consultation: per-session fee or subscription package.
- Remote patient monitoring: monthly subscription.
- Clinic/hospital solution: B2B pricing by patient count or account count.

## Project Structure

This repository currently contains two main parts:

```text
annhien-care/
|-- backend/   # Laravel API, admin, authorization, records, schedules, logs
|-- mobile/    # Flutter app for patients, families, and care staff
`-- README.md
```

Current technology stack:

- Backend: Laravel, Sanctum, Redis/Predis, Spatie Permission, Spatie Activity Log.
- Backend frontend assets: Vite, Tailwind CSS.
- Mobile: Flutter, Riverpod, GoRouter, Dio, Secure Storage, Firebase Messaging, Local Notifications.
- Default local backend database: SQLite.

## Run The Backend

Requirements:

- PHP 8.3+
- Composer
- Node.js and npm

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

Start the Laravel server:

```bash
php artisan serve
```

Or run the combined development workflow defined in `composer.json`:

```bash
composer run dev
```

## Run The Mobile App

Requirements:

- Flutter SDK compatible with Dart `^3.12.2`
- Android Studio/Xcode or a supported device/emulator

```bash
cd mobile
flutter pub get
flutter run
```

## Implementation Roadmap

1. Research: interview 15-30 caregivers and 5-10 healthcare professionals, then choose one target patient group.
2. MVP: build records, document scanning, care schedules, journals, and reports using simulated data.
3. Supervised pilot: work with one clinic or nurse group, measure OCR/AI error rates and false-alert rates.
4. Narrow commercialization: launch family plans and booking services in one area, then improve verification, payment, and incident handling.
5. B2B expansion: integrate with clinics/hospitals, monitoring devices, and operational management systems.

## Metrics To Track

- Safety: medication fields corrected by reviewers, missed alerts, incidents per 1,000 sessions.
- Effectiveness: medication schedule completion rate, profile creation time, reports viewed by doctors.
- Experience: weekly active users, retention rate, caregiver satisfaction score.
- Operations: booking acceptance rate, on-time arrival rate, complaint rate, incident resolution time.
- Business: paid conversion rate, average revenue, retention, customer acquisition cost.

## Medical And Legal Notes

This project handles health data, location data, care journals, and workflows that may relate to healthcare services. Before testing with real patient data or charging for healthcare-related services, the project should be reviewed by medical advisors, security specialists, and legal counsel in Vietnam.

This README is an initial product and technical orientation document. It is not medical or legal advice.
