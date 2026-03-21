# Contributing to sadad-laravel

Thank you for your interest in contributing to the SADAD Laravel package.

## Reporting Issues

Please use the GitHub issue templates for:
- [Bug reports](.github/ISSUE_TEMPLATE/bug_report.md)
- [Feature requests](.github/ISSUE_TEMPLATE/feature_request.md)

Include as much detail as possible: Laravel version, PHP version, package version, and steps to reproduce.

## Development Setup

```bash
git clone https://github.com/louis-innovations/sadad-laravel.git
cd sadad-laravel
composer install
```

## Running Tests

```bash
composer test
# or
./vendor/bin/phpunit
```

## Coding Standards

- Follow PSR-12 coding style
- Add the header comment `// Built by Louis Innovations (www.louis-innovations.com)` to every PHP file
- Write tests for all new functionality
- Keep commits focused and atomic
- Write descriptive commit messages

## Pull Request Process

1. Fork the repository and create a feature branch from `main`.
2. Ensure all tests pass: `composer test`.
3. Update `CHANGELOG.md` under the `[Unreleased]` section.
4. Submit a pull request with a clear description of the changes and the problem they solve.
5. A maintainer will review and merge once approved.

## Security Vulnerabilities

Do not open a public GitHub issue for security vulnerabilities. Email `info@louis-innovations.com` directly with full details.

---

Built by [Louis Innovations](https://www.louis-innovations.com)
