## Description

<!-- Provide a brief description of the changes in this PR -->

## Type of Change

- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Performance improvement
- [ ] Code refactoring
- [ ] Dependency update

## Related Issues

<!-- Link to related issues using #issue_number -->
Fixes #
Relates to #

## Changes Made

<!-- List the main changes made in this PR -->

- 
- 
- 

## Testing

### Test Coverage

- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] Browser tests added/updated (if UI changes)
- [ ] All tests passing locally

### Manual Testing

<!-- Describe manual testing performed -->

**Steps to test:**
1. 
2. 
3. 

**Expected behavior:**


**Actual behavior:**


## Screenshots

<!-- If applicable, add screenshots to help explain your changes -->

## Checklist

### Code Quality

- [ ] Code follows PSR-12 standards
- [ ] Ran `./vendor/bin/pint` and code is formatted
- [ ] Ran `./vendor/bin/phpstan` with no errors
- [ ] No debug code or console.logs left behind
- [ ] Comments added for complex logic

### Security

- [ ] No sensitive data exposed (API keys, passwords, etc.)
- [ ] Input validation implemented where needed
- [ ] Authorization checks in place
- [ ] SQL injection prevention (using Eloquent/Query Builder)
- [ ] XSS prevention (proper escaping in Blade)

### Documentation

- [ ] README updated (if needed)
- [ ] API documentation updated (if API changes)
- [ ] Code comments added for complex logic
- [ ] CHANGELOG-ZPANEL.md updated

### Database

- [ ] Migrations are reversible (down() method works)
- [ ] Model $fillable updated for new columns
- [ ] Indexes added for performance
- [ ] Factory updated (if model changed)

### Performance

- [ ] No N+1 queries introduced
- [ ] Eager loading used where appropriate
- [ ] Heavy operations moved to queue jobs
- [ ] Caching implemented for expensive operations

### Backward Compatibility

- [ ] No breaking changes OR breaking changes documented
- [ ] Database migrations are backward compatible
- [ ] API changes are versioned
- [ ] Configuration changes documented in .env.example

## Deployment Notes

<!-- Any special considerations for deploying this change -->

**Required actions:**
- [ ] Run migrations: `php artisan migrate`
- [ ] Clear cache: `php artisan optimize:clear`
- [ ] Restart queue workers: `php artisan horizon:terminate`
- [ ] Update environment variables (list below)

**New environment variables:**
```env
# Add any new required environment variables
```

## Additional Context

<!-- Add any other context about the PR here -->

## Review Checklist for Maintainers

- [ ] Code quality meets project standards
- [ ] Tests are comprehensive and passing
- [ ] Documentation is clear and complete
- [ ] Security implications reviewed
- [ ] Performance impact assessed
- [ ] Breaking changes properly communicated

