# Contributing to Laravel API Boilerplate

Thank you for your interest in contributing to the Laravel API Boilerplate! This document outlines the process for contributing to this project.

## 🎯 How to Contribute

There are many ways to contribute to this project:

- **Bug Reports** - Report issues and bugs
- **Feature Requests** - Suggest new features or improvements
- **Code Contributions** - Submit pull requests with bug fixes or new features
- **Documentation** - Improve or add documentation
- **Testing** - Add or improve tests
- **Security** - Report security vulnerabilities

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL or PostgreSQL
- Git

### Development Setup

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/ysnskrtan/laravel-api-boilerplate.git
   cd laravel-api-boilerplate
   ```

3. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

4. **Set up environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Set up database**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Run tests** to ensure everything works:
   ```bash
   php artisan test
   ```

## 📋 Development Workflow

### Creating a Feature Branch

```bash
# Create and switch to a new feature branch
git checkout -b feature/your-feature-name

# Or for bug fixes
git checkout -b fix/bug-description
```

### Making Changes

1. **Write code** following our coding standards
2. **Add tests** for new functionality
3. **Update documentation** if needed
4. **Run tests** to ensure nothing breaks
5. **Commit changes** with clear messages

### Submitting Changes

1. **Push your branch** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

2. **Create a Pull Request** on GitHub with:
   - Clear title and description
   - Reference to related issues
   - Screenshots (if applicable)
   - Test coverage information

## 🧪 Testing Guidelines

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/AuthenticationTest.php

# Run tests with coverage
php artisan test --coverage

# Run tests with detailed output
php artisan test --verbose
```

### Writing Tests

- **Feature tests** for API endpoints
- **Unit tests** for individual classes/methods
- **Integration tests** for complex workflows
- Follow existing test patterns and naming conventions

Example test structure:
```php
<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user can view their profile', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);
    
    $response = $this->getJson('/api/user');
    
    $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
});
```

## 📝 Coding Standards

### PHP Standards

- Follow **PSR-12** coding standards
- Use **Laravel best practices**
- Write **clear, self-documenting code**
- Add **PHPDoc comments** for complex methods

### Code Style

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\ApiController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->allowedFilters(['name', 'email'])
            ->allowedSorts(['name', 'created_at'])
            ->paginate($request->get('per_page', 15));

        return $this->successResponse(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }
}
```

### Database Migrations

- Use **descriptive migration names**
- Add **foreign key constraints**
- Include **indexes** for commonly queried columns
- Add **rollback functionality**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
```

## 📚 Documentation Standards

### Code Documentation

- Add **PHPDoc comments** for all public methods
- Document **complex business logic**
- Include **parameter types and return types**
- Add **@throws** annotations for exceptions

### README Updates

- Update relevant sections when adding features
- Include **code examples** for new functionality
- Keep **installation instructions** current
- Update **feature lists** and **tech stack**

### API Documentation

- Document **all endpoints** with examples
- Include **request/response formats**
- Add **authentication requirements**
- Provide **error response examples**

## 🐛 Bug Reports

### Before Reporting

1. **Search existing issues** to avoid duplicates
2. **Test on latest version** to ensure bug exists
3. **Check documentation** for proper usage
4. **Try minimal reproduction** case

### Bug Report Template

```markdown
## Bug Description
Brief description of the issue

## Steps to Reproduce
1. Step one
2. Step two
3. Step three

## Expected Behavior
What should happen

## Actual Behavior
What actually happens

## Environment
- PHP Version: 8.2
- Laravel Version: 12.x
- Database: MySQL 8.0
- OS: Ubuntu 22.04

## Additional Context
Any additional information, screenshots, or logs
```

## 🌟 Feature Requests

### Feature Request Template

```markdown
## Feature Description
Clear description of the proposed feature

## Problem/Use Case
What problem does this solve?

## Proposed Solution
How should this feature work?

## Alternatives Considered
Any alternative approaches considered?

## Implementation Details
Technical details or considerations
```

## 🔒 Security Issues

**Do NOT report security vulnerabilities publicly.**

For security issues:
1. Email: hi@mobita.co
2. Include detailed reproduction steps
3. Wait for acknowledgment before public disclosure
4. Allow reasonable time for fix implementation

## 🎨 UI/UX Contributions

While this is an API-only project, documentation and development tools UI contributions are welcome:

- **Documentation improvements**
- **README enhancements**
- **Development tool configurations**
- **Code organization improvements**

## 📦 Adding Dependencies

### Before Adding Dependencies

1. **Check if really needed** - avoid bloat
2. **Evaluate alternatives** - choose best option
3. **Check maintenance status** - active development
4. **Consider security** - no known vulnerabilities

### Dependency Guidelines

- **Prefer official Laravel packages** when available
- **Use well-maintained packages** with good documentation
- **Avoid packages with security issues**
- **Keep dependencies up to date**

## 🏷️ Versioning

We follow **Semantic Versioning** (SemVer):

- **MAJOR** version for incompatible changes
- **MINOR** version for backward-compatible functionality
- **PATCH** version for backward-compatible bug fixes

## 📋 Pull Request Checklist

Before submitting a PR, ensure:

- [ ] Code follows PSR-12 standards
- [ ] All tests pass
- [ ] New functionality includes tests
- [ ] Documentation is updated
- [ ] Commit messages are clear
- [ ] No merge conflicts
- [ ] Feature branch is up to date

## 🤝 Community Guidelines

### Code of Conduct

- **Be respectful** and professional
- **Welcome newcomers** and help them learn
- **Provide constructive feedback**
- **Focus on the code, not the person**
- **Be patient** with different skill levels

### Communication

- **Use clear, descriptive titles** for issues and PRs
- **Provide context** and background information
- **Be responsive** to feedback and questions
- **Ask for help** when needed
- **Share knowledge** and best practices

## 🏆 Recognition

Contributors will be recognized in:

- **README.md** contributors section
- **Release notes** for significant contributions
- **GitHub releases** acknowledgments
- **Community shoutouts** on social media

## 📞 Getting Help

### Community Support

- **GitHub Issues** - For bugs and feature requests
- **GitHub Discussions** - For questions and community chat
- **Email** - hi@mobita.co

### Development Help

- **Laravel Documentation** - https://laravel.com/docs
- **PHP Documentation** - https://www.php.net/docs.php
- **Pest Documentation** - https://pestphp.com/docs
- **Spatie Packages** - https://spatie.be/docs

## 📝 License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for contributing to Laravel API Boilerplate!** 🚀

Your contributions help make this project better for everyone in the Laravel community. 