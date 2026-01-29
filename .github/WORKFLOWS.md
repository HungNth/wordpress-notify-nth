# NTH Notify - GitHub Workflow Guide

## Release Workflow

This guide explains how to create a new release for the NTH Notify WordPress plugin.

### Prerequisites

1. Ensure all changes are committed and pushed to the main branch
2. Update the version number in `nth-notify.php` (line 6):
    ```php
    * Version: 1.2.0
    ```
3. Update the version constant in `nth-notify.php` (line 29):
    ```php
    $version = '1.2.0';
    ```
4. Update `changelog.txt` with the new version and changes

### How to Create a Release

#### Method 1: Via GitHub UI (Recommended)

1. Go to your GitHub repository
2. Click on **Actions** tab
3. Select **Release WordPress Plugin** workflow from the left sidebar
4. Click **Run workflow** button (top right)
5. Keep the branch as `main` (or your default branch)
6. Type `yes` in the confirmation field
7. Click **Run workflow** green button

#### Method 2: Via GitHub CLI

```bash
gh workflow run release-plugin.yml -f confirm=yes
```

### What the Workflow Does

1. **Validates confirmation** - Ensures you typed "yes" to proceed
2. **Extracts version** - Reads version from `nth-notify.php`
3. **Checks for duplicates** - Ensures the version tag doesn't already exist
4. **Extracts changelog** - Parses `changelog.txt` for the current version notes
5. **Prepares files** - Creates a clean copy excluding:
    - `.github/` directory
    - `.git/` directory
    - `.agent/` directory
    - `.gitignore` file
    - `build/` directory
    - `node_modules/` directory
    - `.vscode/`, `.idea/` directories
    - Log files, `.DS_Store`, `Thumbs.db`
6. **Creates ZIP** - Packages as `nth-notify.zip` with proper structure:
    ```
    nth-notify.zip
    └── nth-notify/
        ├── nth-notify.php
        ├── includes/
        ├── assets/
        ├── languages/
        └── ... (all plugin files)
    ```
7. **Creates GitHub Release** with:
    - Tag: `v1.2.0`
    - Title: `Release 1.2.0`
    - Description: Changelog + installation instructions
    - Asset: `nth-notify.zip` file
8. **Publishes** - Makes the release public immediately

### Monitoring the Workflow

1. After triggering, click on the workflow run to see progress
2. Each step will show ✅ (success), ⏳ (running), or ❌ (failed)
3. If any step fails, click on it to see detailed error logs

### Changelog Format

The workflow expects `changelog.txt` to follow this format:

```
= 1.2.0 =
* Feature: New awesome feature
* Fix: Fixed annoying bug
* Update: Improved performance

= 1.1.0 =
* Previous version changes...
```

Or Markdown format:

```markdown
## 1.2.0

- Feature: New awesome feature
- Fix: Fixed annoying bug
- Update: Improved performance

## 1.1.0

- Previous version changes...
```

### Troubleshooting

#### "Release already exists"

- Check if a tag `v1.2.0` already exists in GitHub
- Delete the tag/release if needed, or increment the version

#### "Could not extract version"

- Verify `nth-notify.php` has the correct format: `* Version: 1.2.0`
- Ensure there are no extra spaces or characters

#### "No changelog found"

- The release will still be created, just without changelog notes
- Update `changelog.txt` following the format above

#### ZIP file issues

- Ensure the repository is clean (no uncommitted changes)
- Check that required files exist (nth-notify.php, includes/, etc.)

### Manual Release (Without Workflow)

If you need to create a release manually:

```bash
# 1. Create the directory structure
mkdir -p build/nth-notify

# 2. Copy files (excluding unwanted ones)
rsync -av --exclude='.github' --exclude='.git' --exclude='.agent' \
  --exclude='.gitignore' --exclude='build' --exclude='node_modules' \
  ./ build/nth-notify/

# 3. Create ZIP
cd build && zip -r ../nth-notify.zip nth-notify && cd ..

# 4. Create release on GitHub manually
# Upload nth-notify.zip to the release
```

### Best Practices

1. **Test before release**: Always test the plugin locally before creating a release
2. **Version consistency**: Ensure version numbers match in all files
3. **Meaningful changelog**: Write clear, user-friendly changelog entries
4. **Semantic versioning**: Follow semver (MAJOR.MINOR.PATCH)
    - MAJOR: Breaking changes
    - MINOR: New features (backward compatible)
    - PATCH: Bug fixes
5. **Branch protection**: Consider protecting your main branch to require PR reviews

### Security Notes

- The workflow uses `GITHUB_TOKEN` which is automatically provided by GitHub Actions
- No manual secrets configuration needed
- The token has limited permissions scoped to the repository
- Releases are public by default; set `draft: true` if you want to review first

### Workflow File Location

The workflow file is located at:

```
.github/workflows/release-plugin.yml
```

To modify the workflow behavior, edit this file and commit the changes.
