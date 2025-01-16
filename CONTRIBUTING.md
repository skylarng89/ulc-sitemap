# Contributing to ULC Sitemap

We love your input! We want to make contributing to ULC Sitemap as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

## We Develop with Github
We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

## We Use [Github Flow](https://guides.github.com/introduction/flow/index.html)
Pull requests are the best way to propose changes to the codebase. We actively welcome your pull requests:

1. Fork the repo and create your branch from `master`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code follows WordPress Coding Standards.
6. Issue that pull request!

## Any contributions you make will be under the GPL v2 Software License
In short, when you submit code changes, your submissions are understood to be under the same [GPL v2 License](http://choosealicense.com/licenses/gpl-2.0/) that covers the project. Feel free to contact the maintainers if that's a concern.

## Report bugs using Github's [issue tracker](https://github.com/skylarng89/ulc-sitemap/issues)
We use GitHub issues to track public bugs. Report a bug by [opening a new issue](https://github.com/skylarng89/ulc-sitemap/issues/new); it's that easy!

## Write bug reports with detail, background, and sample code

**Great Bug Reports** tend to have:

- A quick summary and/or background
- Steps to reproduce
  - Be specific!
  - Give sample code if you can.
- What you expected would happen
- What actually happens
- Notes (possibly including why you think this might be happening, or stuff you tried that didn't work)

## Development Process

1. Clone the repository
```bash
git clone https://github.com/skylarng89/ulc-sitemap.git
```

2. Install dependencies
```bash
composer install
```

3. Set up testing environment
```bash
bin/install-wp-tests.sh wordpress_test root password localhost latest
```

4. Run tests
```bash
./vendor/bin/phpunit
```

5. Check coding standards
```bash
composer phpcs
```

## Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use meaningful variable and function names
- Comment your code where necessary
- Write tests for new features
- Keep functions focused and maintainable
- Use WordPress core functions when available

## Pull Request Process

1. Update the README.md with details of changes to the interface, if applicable.
2. Update the tests as needed.
3. Update the documentation to reflect any changes.
4. The PR will be merged once you have the sign-off of one other developer.

## License
By contributing, you agree that your contributions will be licensed under its GPL v2 License.

## References
This document was adapted from the open-source contribution guidelines for [Facebook's Draft](https://github.com/facebook/draft-js/blob/a9316a723f9e918afde44dea68b5f9f39b7d9b00/CONTRIBUTING.md).