<p align="center">
  <img src="public/denr_logo.png" alt="DENR logo" width="110">
</p>

<h1 align="center">DENR-CAR Personnel Information System</h1>

<p align="center">
  A web application for maintaining personnel records, managing position assignments, and monitoring staffing across DENR-CAR offices.
</p>

## About the Project

The DENR-CAR Personnel Information System (CARPIS) supports the Department of Environment and Natural Resources - Cordillera Administrative Region in managing employee information, organizational assignments, plantilla positions, and user accounts.

The system provides a centralized workspace for authorized personnel in the Regional Office, Provincial Environment and Natural Resources Offices (PENROs), and Community Environment and Natural Resources Offices (CENROs). Administrators maintain records within their assigned office scope, while employees can view their personal profile and manage account settings.

## Features

- Employee records covering personal details, contact information, employment status, education, eligibility, and appointment details.
- Employee search and filtering by assignment, organizational unit, employment status, and position.
- Position tracking, including item codes, salary grades, vacancy status, and occupant details.
- Position assignment history and employee archiving and restoration.
- Excel employee imports with batch progress and error feedback, plus workbook downloads for filtered records.
- Reference data management for organizational units, positions, salary grades, and assignment places.
- Dashboard summaries and charts for personnel and staffing.
- User account management, role assignments, office scopes, and account archiving and restoration.
- Personal profiles, password reset, password changes, and appearance settings.

## Technology Stack

| Layer | Technologies |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, Eloquent ORM |
| Frontend | Vue 3, TypeScript, Inertia.js 2 |
| Styling and UI | Tailwind CSS 4, Reka UI, Lucide icons |
| Asset build | Vite 7, npm |
| Database | Laravel database connections, including SQLite and MySQL |
| Charts | Chart.js, Vue Chart.js |
| Spreadsheet processing | PhpSpreadsheet |
| Automated testing | Pest 3, Laravel test utilities |

## System Workflow

1. **Sign in:** A user enters their account credentials. Ordinary users are directed to their personal profile, while administrators can access the dashboard and administrative modules.
2. **Apply office scope:** Administrators access records within their assigned office scope. Super Admin accounts can work across offices.
3. **Maintain personnel records:** Authorized users enter employee details, select organizational assignments and available positions, and update related personnel information.
4. **Import or download records:** Administrators upload employee Excel workbooks for batch processing or download records using the current filters.
5. **Archive or restore records:** Authorized users archive or restore employee and user records, with position availability and assignment history updated as applicable.
6. **Monitor staffing:** Administrators review dashboard summaries and personnel charts for their permitted offices.

## Security & Access

The application uses password authentication, hashed passwords, session management, login throttling, and server-side validation. Administrative routes require administrator access, and organizational scope checks restrict applicable personnel and user operations.

Ordinary users access their own personnel profile and account settings. Admin accounts manage records within their assigned office scope, while Super Admin accounts have administrative access across offices.

Email verification routes are present, but the current User model does not implement Laravel's email verification contract, so email verification is not currently enforced by the verified middleware.

Operational access is intended for authorized DENR-CAR personnel. Account roles and office assignments should be managed by designated administrators according to each user's responsibilities.

Credentials, personnel datasets, environment configuration, database backups, and internal deployment details should be kept out of public documentation and shared only through approved internal channels.

## Source Code Availability

Source code access is intended to be restricted to authorized project maintainers and collaborators. Requests for access should be directed to the project owner or designated DENR-CAR system administrator for review.

This README provides an overview of the project, its features, and its intended access policy.

Repository visibility and collaborator permissions must be configured on the repository hosting platform to enforce this access policy. Updating this README does not change repository visibility or revoke access to source code or previously published content.
