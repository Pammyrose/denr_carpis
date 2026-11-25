$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.IO.Compression

$source = 'C:\Users\emala\OneDrive\Documents\PMS - Copy\deliverables\PMS_Manual_User_Revised.docx'
$destination = Join-Path $PSScriptRoot 'CARPIS_User_Manual.docx'
Copy-Item -LiteralPath $source -Destination $destination -Force

$content = @'
SUB|DENR-CAR – CAR Personnel Information System (CARPIS)
SUB|USER MANUAL
H2|Document Control
P|Version: 1.0
P|Prepared for: Department of Environment and Natural Resources – Cordillera Administrative Region
P|Date: 25 November 2026
P|Purpose: Instructions for personnel users, office administrators, and super administrators.
H2|Contents
P|1. Introduction and User Roles
P|2. Accessing CARPIS
P|3. Dashboard and Navigation
P|4. Employee Records
P|5. Excel Import and Download
P|6. Positions and Reference Data
P|7. Archived Employees
P|8. User Accounts
P|9. Personal Profile and Settings
P|10. Troubleshooting and Glossary
H1|1. Introduction and User Roles
H2|1.1 Purpose
P|CARPIS is the DENR-CAR web application for personnel records, office assignments, plantilla positions, and account administration. This manual explains the screens and actions available in the current application. The administrator supplies the approved website address and account credentials.
H2|1.2 Roles and access
STEP|• User: Opens their own personnel profile and account settings.
STEP|• Admin: Uses Dashboard, Employee, Positions, and Users within their assigned office scope. A PENRO scope can include configured subordinate CENRO offices.
STEP|• Super Admin: Uses the administrative modules across offices and may assign account roles.
P|The sidebar reflects the signed-in role. If an expected office or record is absent, ask the account administrator to check your role and office scope before changing data.
H2|1.3 Before you begin
STEP|• Use a browser on the network where CARPIS is hosted and obtain the approved site address.
STEP|• Prepare verified personal and employment details, office assignment, and position item code.
STEP|• For bulk import, prepare an .xlsx or .xls workbook in the supported format.
H1|2. Accessing CARPIS
H2|2.1 Sign in
STEP|1. Open the approved CARPIS website address.
STEP|2. Enter your registered email address and password on the Login page.
STEP|3. Select Log in. Admin and Super Admin accounts can open the administrative dashboard; an ordinary User is directed to their profile.
CAP|Figure 1. CARPIS Login page
SHOT|(Put screenshot picture here)
P|If sign-in fails, check the email and password, then use the password reset link if available. Ask an administrator to check whether the account is active.
H2|2.2 Sign out
STEP|1. Finish and verify any pending edits.
STEP|2. Open the account menu and select Log out.
STEP|3. Confirm that the Login page appears, especially on a shared computer.
H1|3. Dashboard and Navigation
H2|3.1 Sidebar
P|The administrator sidebar contains Dashboard, Employee, Positions, and Users. An ordinary User sees Profile. Select a module to open it; use the account menu for Log out.
H2|3.2 Review staffing information
STEP|1. Select Dashboard.
STEP|2. Choose an Assignment and, if needed, a division or section from the available filters.
STEP|3. Review the displayed personnel totals, filled positions, vacancies, and charts.
STEP|4. Confirm the selected filters and office scope before interpreting a total.
CAP|Figure 2. Dashboard filters and staffing summary
SHOT|(Put screenshot picture here)
P|The dashboard summarizes records currently stored in CARPIS. Correct underlying employee or position data through the appropriate module if a figure looks wrong.
H1|4. Employee Records
H2|4.1 Locate an employee
STEP|1. Select Employee in the sidebar.
STEP|2. Use Assignment, Division, and Position filters. Choose an assignment before a division when the division control requires it.
STEP|3. Type a name or other available detail in Search to narrow the table.
STEP|4. Open the employee's View action to inspect their information and position history.
CAP|Figure 3. Employee list, filters, and record actions
SHOT|(Put screenshot picture here)
H2|4.2 Create an employee
STEP|1. On Employee, select Create.
STEP|2. Enter the employee's name, sex, birth and contact details, and other requested personal information.
STEP|3. Choose the assignment place and division/section/unit, then choose an available position. The position list is limited by organizational unit and vacancy; the salary grade fills from the selected position.
STEP|4. Enter employment status, email, appointment details, education, eligibility, and any other applicable fields.
STEP|5. Review required fields and select the form's submit button. Correct any validation messages and submit again.
CAP|Figure 4. Create Employee form and position selection
SHOT|(Put screenshot picture here)
P|Expected result: The employee appears in the list, and the assigned position is treated as occupied. Check the chosen office and item code before saving.
H2|4.3 Edit or view an employee
STEP|1. Find the employee on Employee and select Edit or View.
STEP|2. Change only verified details. If changing the assignment or position, select the correct organizational unit and an available position.
STEP|3. Save, then reopen the record to confirm the updated details and position history.
CAP|Figure 5. Employee details and position history
SHOT|(Put screenshot picture here)
H2|4.4 Archive an employee
STEP|1. Find the employee and select Archive.
STEP|2. Read the confirmation and confirm only for the intended record.
STEP|3. Open Archived Employees to verify that the record moved there.
P|Archiving removes the employee from the active list and releases the occupied position when applicable. An archived record can be restored using the archived view.
H1|5. Excel Import and Download
H2|5.1 Import employees
STEP|1. On Employee, select the Excel import/upload control and choose an .xlsx or .xls workbook.
STEP|2. Start the upload and keep the Employee page open while processing.
STEP|3. Watch the row progress and the counts for created, updated, and failed records.
STEP|4. Review listed errors, correct the source workbook, and import corrected rows as needed.
CAP|Figure 6. Excel import progress and results
SHOT|(Put screenshot picture here)
P|An import can update existing employees. Check the workbook's identities, office assignments, and positions before upload. Import access is limited by your account's office scope.
H2|5.2 Download employee data
STEP|1. On Employee, set Assignment, Division, and Position filters as needed.
STEP|2. Select the Excel download control.
STEP|3. Open the downloaded workbook and verify that its records match the selected scope.
P|The download follows the current employee filters and your permitted office scope.
H1|6. Positions and Reference Data
H2|6.1 Review positions
STEP|1. Select Positions in the sidebar.
STEP|2. Review Total positions, Vacant, and Occupied counts.
STEP|3. Filter by office, position name, or status, or use Search for an item code, organizational code, title, salary grade, or occupant.
STEP|4. Open a position to inspect its details and occupant information, if occupied.
CAP|Figure 7. Positions list and status filters
SHOT|(Put screenshot picture here)
H2|6.2 Add a position
STEP|1. On Positions, select Add Position.
STEP|2. Enter the requested position name, item code, organizational unit, salary grade, and related details.
STEP|3. Save and confirm that the new item appears in the correct office and vacancy list.
P|Employee creation also provides controls for adding relevant reference entries such as organizational units, positions, salary grades, and assignment places. Use these only when an approved value is missing; duplicate reference data can make filtering and assignment confusing.
H1|7. Archived Employees
H2|7.1 Restore a record
STEP|1. On Employee, select Archived Employees.
STEP|2. Search for the archived employee and review their details.
STEP|3. Select Restore/Unarchive. Choose an available position if the former item is occupied or no longer available.
STEP|4. Confirm the restoration and verify the employee in the active list and the position status in Positions.
CAP|Figure 8. Archived Employees and restore dialog
SHOT|(Put screenshot picture here)
P|A former position may no longer be vacant. Do not select a different item without checking the approved personnel assignment.
H1|8. User Accounts
H2|8.1 Find and create users
STEP|1. Select Users in the sidebar and use Search to locate an account.
STEP|2. To add an account, select Create user.
STEP|3. Enter the requested identity, email, password, role, and office scope information shown by your role.
STEP|4. Save and confirm the user appears in the list.
CAP|Figure 9. Users list and Create user form
SHOT|(Put screenshot picture here)
P|Only Super Admin accounts can assign all account roles. Admin accounts are restricted to their allowed office scope and account permissions.
H2|8.2 Update, archive, and restore users
STEP|1. Find the account and open its View or Edit action.
STEP|2. Make the authorized changes and save. Confirm the account's role and office scope afterward.
STEP|3. To deactivate a user record, use Archive and confirm the account.
STEP|4. Use the archived users view and Restore/Unarchive to return an account when appropriate.
P|Take care when changing an account's office scope: it determines which personnel records an administrator can see and manage.
H1|9. Personal Profile and Settings
H2|9.1 View your profile
STEP|1. Select Profile from the sidebar if you have a User account, or open the profile route available to your account.
STEP|2. Review the linked personnel information. If an employment detail is incorrect, ask an administrator to update the employee record.
H2|9.2 Account settings
P|Account settings provide profile information, password changes, and appearance preferences. Open the relevant settings page, enter the requested values, save, and check for a confirmation or validation message. Use a strong password and sign out after working on a shared device.
CAP|Figure 10. Profile and account settings
SHOT|(Put screenshot picture here)
H1|10. Troubleshooting and Glossary
H2|10.1 Common issues
STEP|• Cannot sign in: Check the registered email and password; use password reset or ask the account administrator to check account status.
STEP|• Missing office or employee: Clear filters, then confirm your office scope with an administrator.
STEP|• Position unavailable: Check the organizational unit and whether the position is already occupied.
STEP|• Form will not save: Read validation messages and complete required fields; check for an existing record or position conflict.
STEP|• Excel import has failed rows: Review the row errors and correct the workbook before retrying.
STEP|• Restored employee cannot take former position: Select an approved vacant item after checking the current position list.
H2|10.2 Terms
STEP|CARPIS — CAR Personnel Information System.
STEP|DENR-CAR — Department of Environment and Natural Resources, Cordillera Administrative Region.
STEP|PENRO / CENRO — Provincial / Community Environment and Natural Resources Office.
STEP|Office scope — The offices an administrator is allowed to view and manage.
STEP|Plantilla position — A position item identified by an item code and organizational assignment.
STEP|Archived — Removed from active lists while retained for possible restoration.
H2|10.3 Support
P|For an access or data issue, record the page, the affected employee or item code, the exact error message, and the time it occurred. Share those details with the designated CARPIS administrator through your organization's approved support channel. Do not include passwords in support messages.
'@

function Escape-Xml([string]$value) { return [System.Security.SecurityElement]::Escape($value) }
$paragraphs = [System.Text.StringBuilder]::new()
foreach ($line in ($content -split "`r?`n")) {
    if (-not $line.Trim()) { continue }
    $parts = $line.Split('|', 2)
    $style = switch ($parts[0]) { 'SUB' { 'Subtitle' } 'H1' { 'Heading1' } 'H2' { 'Heading2' } 'STEP' { 'ProcedureStep' } default { 'Normal' } }
    $safe = Escape-Xml $parts[1]
    $extra = if ($parts[0] -eq 'SHOT') { '<w:jc w:val="center"/><w:spacing w:before="120" w:after="240"/>' } else { '' }
    $runExtra = if ($parts[0] -eq 'SHOT') { '<w:rPr><w:i/><w:color w:val="777777"/></w:rPr>' } elseif ($parts[0] -eq 'CAP') { '<w:rPr><w:b/></w:rPr>' } else { '' }
    [void]$paragraphs.Append('<w:p><w:pPr><w:pStyle w:val="' + $style + '"/>' + $extra + '</w:pPr><w:r>' + $runExtra + '<w:t xml:space="preserve">' + $safe + '</w:t></w:r></w:p>')
}

$zip = [IO.Compression.ZipFile]::Open($destination, [IO.Compression.ZipArchiveMode]::Update)
try {
    $entry = $zip.GetEntry('word/document.xml')
    $reader = [IO.StreamReader]::new($entry.Open())
    $original = $reader.ReadToEnd()
    $reader.Close()
    $section = [regex]::Match($original, '<w:sectPr\b[\s\S]*?</w:sectPr>').Value
    $section = [regex]::Replace($section, '<w:headerReference\b[^>]*/>', '')
    $section = [regex]::Replace($section, '<w:footerReference\b[^>]*/>', '')
    $start = $original.Substring(0, $original.IndexOf('<w:body>') + 8)
    $newXml = $start + $paragraphs.ToString() + $section + '</w:body></w:document>'
    $entry.Delete()
    $newEntry = $zip.CreateEntry('word/document.xml', [IO.Compression.CompressionLevel]::Optimal)
    $writer = [IO.StreamWriter]::new($newEntry.Open(), [Text.UTF8Encoding]::new($false))
    $writer.Write($newXml)
    $writer.Close()
    foreach ($oldPart in @($zip.Entries | Where-Object { $_.FullName -match '^word/media/|^word/header\d+\.xml$|^word/footer\d+\.xml$|^customXml/' })) {
        $oldPart.Delete()
    }
    foreach ($partName in @('word/_rels/document.xml.rels', '[Content_Types].xml')) {
        $part = $zip.GetEntry($partName)
        $partReader = [IO.StreamReader]::new($part.Open())
        $partXml = $partReader.ReadToEnd()
        $partReader.Close()
        $partXml = [regex]::Replace($partXml, '<Relationship\b[^>]*(?:Target="(?:media/|header|footer|\.\./customXml/)|Type="[^"]*/customXml")[^>]*/>', '')
        $partXml = [regex]::Replace($partXml, '<Override\b[^>]*PartName="/(?:word/(?:header|footer)\d+\.xml|customXml/)[^"]*"[^>]*/>', '')
        $part.Delete()
        $replacement = $zip.CreateEntry($partName)
        $partWriter = [IO.StreamWriter]::new($replacement.Open(), [Text.UTF8Encoding]::new($false))
        $partWriter.Write($partXml)
        $partWriter.Close()
    }
    $core = $zip.GetEntry('docProps/core.xml')
    $core.Delete()
    $coreNew = $zip.CreateEntry('docProps/core.xml')
    $coreWriter = [IO.StreamWriter]::new($coreNew.Open(), [Text.UTF8Encoding]::new($false))
    $coreWriter.Write('<?xml version="1.0" encoding="UTF-8"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>CARPIS User Manual</dc:title><dc:subject>DENR-CAR Personnel Information System</dc:subject></cp:coreProperties>')
    $coreWriter.Close()
} finally { $zip.Dispose() }
Write-Output $destination
