# ULC Sitemap

A fast and secure XML sitemap generator for WordPress with advanced caching and customization options.

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/ulc-sitemap)](https://wordpress.org/plugins/ulc-sitemap/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/rating/ulc-sitemap)](https://wordpress.org/plugins/ulc-sitemap/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/ulc-sitemap)](https://wordpress.org/plugins/ulc-sitemap/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## Features

- 🚀 Automatic sitemap generation
- 💾 Advanced caching system for optimal performance
- 🔄 Support for posts, pages, custom post types, categories, tags, and author archives
- ⚙️ Customizable priorities and change frequencies
- 🔍 SEO-friendly URL structure
- 🌐 Multi-language support
- 📊 Performance monitoring and reporting
- 🔌 WordPress REST API integration

## Requirements

- WordPress 5.9 or higher
- PHP 8.2 or higher
- MySQL 5.7 or higher

## Installation

### From WordPress.org

1. Visit Plugins > Add New
2. Search for "ULC Sitemap"
3. Install and activate the plugin

### Manual Installation

1. Download the latest release from the [releases page](https://github.com/skylarng89/ulc-sitemap/releases)
2. Upload the plugin folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

## Development

### Requirements

- PHP 8.2+
- Composer
- PHPUnit
- WordPress Test Suite

### Setup

```bash
# Clone the repository
git clone https://github.com/skylarng89/ulc-sitemap.git
cd ulc-sitemap

# Install dependencies
composer install

# Set up WordPress test environment
bin/install-wp-tests.sh wordpress_test root password localhost latest

# Run tests
./vendor/bin/phpunit
```

### Building

```bash
# Run PHP CodeSniffer
composer phpcs

# Fix coding standards
composer phpcbf

# Run tests with coverage
composer test
```

## Features

### Search Engine Integration

- Google Search Console
- Bing Webmaster Tools
- Yandex Webmaster
- Baidu Webmaster Tools

### Schema.org Support

- Article
- WebPage
- Product
- Organization
- BreadcrumbList

### Performance Features

- Efficient caching system
- Minimal database queries
- Optimized for large websites
- Low memory footprint

## Documentation

Visit our [documentation](https://github.com/skylarng89/ulc-sitemap/wiki) for detailed information on:

- Configuration options
- Advanced usage
- API endpoints
- Performance optimization
- Troubleshooting

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## Support

- [WordPress.org Support Forum](https://wordpress.org/support/plugin/ulc-sitemap/)
- [GitHub Issues](https://github.com/skylarng89/ulc-sitemap/issues)
- [Documentation](https://github.com/skylarng89/ulc-sitemap/wiki)

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes.

## Credits

Created and maintained by [Patrick Aziken](https://github.com/skylarng89).

## Security

If you discover any security-related issues, please email security@upperloftcreations.com instead of using the issue tracker.

## Roadmap

- [ ] Advanced XML Schema validation
- [ ] Image sitemap support
- [ ] Video sitemap support
- [ ] News sitemap support
- [ ] Enhanced performance monitoring
- [ ] Additional schema types
- [ ] More search engine integrations