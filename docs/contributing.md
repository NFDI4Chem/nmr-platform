# Contributing to NMR Platform

Thank you for your interest in contributing to the NMR Platform! This guide will help you get started with contributing to the project.

## Ways to Contribute

There are many ways you can contribute to the NMR Platform:

- 🐛 **Report bugs** and suggest fixes
- 🚀 **Request features** and enhancements
- 💻 **Submit code** improvements and new features
- 📖 **Improve documentation** and examples
- 🧪 **Write tests** and improve test coverage
- 🎨 **Improve UI/UX** and user experience
- 🌍 **Translate** the platform to other languages
- 🔍 **Review code** and provide feedback

## Getting Started

### 1. Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/your-username/nmr-platform.git
   cd nmr-platform
   ```

3. Add the upstream repository:
   ```bash
   git remote add upstream https://github.com/NFDI4Chem/nmr-platform.git
   ```

### 2. Set Up Development Environment

Follow the [Developer Setup Guide](/developer/setup) to set up your local development environment.

### 3. Create a Branch

Create a new branch for your contribution:

```bash
git checkout -b feature/your-feature-name
# or
git checkout -b fix/bug-description
# or
git checkout -b docs/documentation-improvement
```

## Development Workflow

### 1. Code Style

We follow PSR-12 coding standards for PHP and maintain consistent JavaScript/Vue.js code style.

**Check code style:**
```bash
./vendor/bin/pint --test
npm run lint
```

**Fix code style:**
```bash
./vendor/bin/pint
npm run lint:fix
```

### 2. Testing

Write tests for your code and ensure all tests pass:

```bash
# Run PHP tests
php artisan test

# Run JavaScript tests
npm run test

# Run tests with coverage
php artisan test --coverage
```

### 3. Documentation

Update documentation when adding new features:

- Update API documentation for new endpoints
- Add inline code comments for complex logic
- Update user guides for new features
- Include examples in documentation

## Commit Guidelines

### Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/) for commit messages:

```
type(scope): description

[optional body]

[optional footer]
```

### Types

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Changes that do not affect the meaning of the code
- **refactor**: A code change that neither fixes a bug nor adds a feature
- **perf**: A code change that improves performance
- **test**: Adding missing tests or correcting existing tests
- **chore**: Changes to the build process or auxiliary tools

### Examples

```bash
feat(api): add dataset comparison endpoint

Add new API endpoint to compare multiple datasets and return
similarity metrics and overlay data.

Closes #123
```

```bash
fix(upload): handle large file uploads correctly

Fix memory issues when uploading files larger than 100MB by
implementing chunked upload processing.

Fixes #456
```

```bash
docs(api): update authentication examples

Add examples for token refresh and error handling in the
API authentication documentation.
```

## Pull Request Process

### 1. Before Submitting

- [ ] Code follows project style guidelines
- [ ] All tests pass
- [ ] Documentation is updated
- [ ] Commit messages follow conventional format
- [ ] Branch is up to date with main

### 2. Pull Request Template

When creating a pull request, include:

**Description:**
- Clear description of what the PR does
- Link to related issues
- Screenshots for UI changes

**Changes:**
- List of changes made
- Breaking changes (if any)
- Migration requirements

**Testing:**
- How to test the changes
- Test cases covered
- Manual testing steps

**Checklist:**
- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] Code style guidelines followed
- [ ] Breaking changes documented

### 3. Review Process

1. **Automated Checks**: CI will run tests and checks
2. **Code Review**: Maintainers will review your code
3. **Feedback**: Address any feedback or requested changes
4. **Approval**: Once approved, your PR will be merged

## Issue Guidelines

### Reporting Bugs

When reporting bugs, please include:

**Environment:**
- PHP version
- Laravel version
- Browser (for frontend issues)
- Operating system

**Steps to Reproduce:**
1. Step-by-step instructions
2. Expected behavior
3. Actual behavior
4. Screenshots/logs if applicable

**Minimal Example:**
- Provide minimal code to reproduce the issue
- Include relevant configuration

### Feature Requests

For feature requests, please include:

**Problem Statement:**
- What problem does this solve?
- Who would benefit from this feature?

**Proposed Solution:**
- Detailed description of the feature
- How it should work
- Alternative solutions considered

**Additional Context:**
- Mock-ups or examples
- Related issues or discussions

## Code Review Guidelines

### For Contributors

When your code is being reviewed:

- **Be responsive** to feedback
- **Ask questions** if feedback is unclear
- **Test suggestions** before implementing
- **Be open** to different approaches

### For Reviewers

When reviewing code:

- **Be constructive** and helpful
- **Explain reasoning** behind suggestions
- **Praise good work** and improvements
- **Focus on code quality** and maintainability

## Development Best Practices

### PHP/Laravel

1. **Follow Laravel conventions**:
   - Use Eloquent relationships properly
   - Follow naming conventions
   - Use proper directory structure

2. **Write clean code**:
   - Use descriptive variable and method names
   - Keep methods small and focused
   - Add comments for complex logic

3. **Handle errors gracefully**:
   - Use proper exception handling
   - Provide meaningful error messages
   - Log errors appropriately

### Frontend (Vue.js/JavaScript)

1. **Component structure**:
   - Keep components small and reusable
   - Use proper prop validation
   - Follow Vue.js style guide

2. **State management**:
   - Use Vuex/Pinia for complex state
   - Keep components stateless when possible
   - Avoid prop drilling

3. **Performance**:
   - Optimize bundle size
   - Use lazy loading for routes
   - Implement proper caching

### Database

1. **Migrations**:
   - Always use migrations for schema changes
   - Never modify existing migrations
   - Include proper rollback logic

2. **Queries**:
   - Use eager loading to avoid N+1 queries
   - Add proper database indexes
   - Optimize slow queries

### API Design

1. **RESTful principles**:
   - Use proper HTTP methods
   - Follow REST conventions
   - Implement proper status codes

2. **Documentation**:
   - Document all endpoints
   - Include request/response examples
   - Keep documentation up to date

## Security Guidelines

### General Security

- **Validate all input** from users
- **Sanitize output** to prevent XSS
- **Use parameterized queries** to prevent SQL injection
- **Implement proper authentication** and authorization
- **Keep dependencies updated** regularly

### Sensitive Data

- **Never commit secrets** to version control
- **Use environment variables** for configuration
- **Encrypt sensitive data** at rest
- **Use HTTPS** for all communications

## Release Process

### Versioning

We follow [Semantic Versioning](https://semver.org/):

- **Major**: Breaking changes
- **Minor**: New features (backward compatible)
- **Patch**: Bug fixes (backward compatible)

### Release Notes

Each release includes:

- New features and improvements
- Bug fixes
- Breaking changes
- Migration instructions
- Security updates

## Community Guidelines

### Code of Conduct

We are committed to providing a welcoming and inclusive environment. Please:

- **Be respectful** and professional
- **Be inclusive** and welcoming to newcomers
- **Be constructive** in discussions and feedback
- **Report inappropriate behavior** to maintainers

### Communication Channels

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions and community discussions
- **Pull Requests**: Code contributions and reviews
- **Email**: security@nmrplatform.org for security issues

## Recognition

We value all contributions and recognize contributors in:

- **Release notes** for significant contributions
- **Contributors file** for all code contributors
- **Documentation** for documentation improvements
- **Community highlights** for helping other users

## Getting Help

If you need help with contributing:

1. **Check existing documentation** first
2. **Search existing issues** for similar questions
3. **Ask in GitHub Discussions** for general help
4. **Contact maintainers** for specific guidance

## License

By contributing to the NMR Platform, you agree that your contributions will be licensed under the same [MIT License](https://opensource.org/licenses/MIT) that covers the project.

---

Thank you for contributing to the NMR Platform! Your efforts help make scientific research more accessible and efficient. 🚀
