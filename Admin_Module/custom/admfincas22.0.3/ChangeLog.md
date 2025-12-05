# CHANGELOG MODULE ADMFINCAS FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## 2.0

**Fixes:**
- Fixed database table structure: renamed column `nom` to `name` for consistency with class definition
- Added missing columns: `label`, `fk_soc`, `fk_project`, `description`, `last_main_doc`, `model_pdf`
- Fixed list view SQL error "Unknown column 't.name' in 'field list'"
- Database now matches the Admfinca class field definitions

**Migration:**
- Automatic migration from v1.0 to v2.0 will update existing installations
- Migration file: `sql/migrations/001_v1_to_v2.sql`

## 1.0

Initial version
