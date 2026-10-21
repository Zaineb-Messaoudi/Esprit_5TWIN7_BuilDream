# Security Policy

## Supported Versions

We actively support the following versions with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 12.x    | ✅ Yes             |
| 11.x    | ❌ No              |
| 10.x    | ❌ No              |
| < 10.x  | ❌ No              |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability, please report it responsibly.

### How to Report

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report them via one of these channels:

1. **GitHub Security Advisories** (Preferred)
   - Go to the [Security tab](https://github.com/Zaineb-Messaoudi/Esprit_5TWIN7_BuilDream/security/advisories/new) of this repository
   - Click "Report a vulnerability"
   - Fill in the details

2. **Email**
   - Send details to: security@solarshare.app
   - Include "SECURITY" in the subject line

3. **Encrypted Communication** (for highly sensitive reports)
   - Request our PGP key via email
   - Encrypt your report before sending

### What to Include

Please provide as much detail as possible:

- **Description** of the vulnerability
- **Steps to reproduce** (if applicable)
- **Impact assessment** (what could an attacker achieve?)
- **Affected versions** (if known)
- **Suggested fix** (if you have one)
- **Your contact information** (for follow-up)

### What to Expect

| Timeline | Action |
|----------|--------|
| **24 hours** | Acknowledgment of receipt |
| **72 hours** | Initial assessment & severity classification |
| **7 days** | Status update (fix in progress / need more info / not a vulnerability) |
| **30 days** | Fix released (for critical/high) or scheduled (for medium/low) |

We will keep you informed throughout the process and credit you in the security advisory (unless you prefer anonymity).

## Security Measures in Place

This project implements several security best practices:

### Application Security
- **Laravel 12** with built-in CSRF, XSS, SQL injection protection
- **Content Security Policy** headers via middleware
- **HTTPS enforcement** in production (HSTS)
- **Rate limiting** on auth, API, and sensitive endpoints
- **Input validation** on all user-facing forms
- **Secure password hashing** (bcrypt, configurable rounds)
- **Two-factor authentication** (TOTP) support

### API & Integration Security
- **Stripe** for all payment processing (PCI DSS compliant)
- **OAuth 2.0** for social authentication (Google, Facebook)
- **Signed URLs** for temporary resource access
- **Webhook signature verification** for all external callbacks
- **API rate limiting** per user/IP

### Infrastructure Security
- **Docker** containers with non-root user
- **MySQL 8.0** with SSL/TLS connections
- **Redis** with authentication & TLS
- **Meilisearch** with master key authentication
- **Ollama** LLM inference on isolated GPU instances
- **Reverb** WebSocket server with JWT authentication

### Secrets Management
- **No secrets in code** - all via environment variables
- **GitHub Secrets** for CI/CD
- **Production secrets** managed via secure vault
- **Regular key rotation** schedule

### Monitoring & Detection
- **Sentry** for error tracking & performance monitoring
- **Laravel Telescope** for local request debugging
- **Audit logging** for sensitive operations
- **Failed login detection** with lockout
- **Suspicious activity alerts**

### Dependency Security
- **Dependabot** weekly updates for Composer & NPM
- **CodeQL** static analysis on every push
- **Composer audit** in CI pipeline
- **NPM audit** in CI pipeline
- **Pinned dependencies** in lock files

## Security Headers

The application sets the following security headers:

```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://api.stripe.com wss://*.solarshare.app; frame-src 'self' https://js.stripe.com; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none';
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=()
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

## Secure Development Practices

Our team follows:

- **Code review** required for all changes
- **Static analysis** (Pint, PHPStan) in CI
- **Security training** for all developers
- **Threat modeling** for new features
- **Penetration testing** annually
- **Incident response plan** documented

## Bug Bounty

We do not currently run a formal bug bounty program. However, we acknowledge and thank security researchers who report vulnerabilities responsibly.

## Contact

For security-related questions (not vulnerability reports):

- **Email**: security@solarshare.app
- **GitHub**: [Security Discussions](https://github.com/Zaineb-Messaoudi/Esprit_5TWIN7_BuilDream/discussions/categories/security)

---

*Last updated: 2026-10-21*
*Policy version: 1.0*