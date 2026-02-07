# Agent & Developer Guidelines

## Branch Rules
- Main branch: `main` (Protected).
- Feature branches: `feature/feature-name`.
- Fix branches: `fix/issue-description`.

## PR Requirements
Every Pull Request must include:
1. **Plan**: A brief explanation of the changes.
2. **Diff Summary**: What files were touched and why.
3. **Evidence**:
   - Output of `composer run lint`.
   - Output of `composer run test`.
4. **Security Notes**: Mention of nonces, sanitization, and capability checks used.

## Coding Conventions
- **PHP Version**: 8.0+.
- **Style**: WordPress Coding Standards (enforced via PHPCS).
- **Security**:
  - Use `wp_nonce_field()` and `check_admin_referer()` for forms.
  - Use `current_user_can()` for permissions.
  - Escape all outputs (`esc_html`, `esc_attr`, etc.).
  - Sanitize all inputs (`sanitize_text_field`, `sanitize_email`, etc.).
