# Google Aspect Types for Coldreader

Aspect Types for [Coldreader](https://github.com/imonroe/coldreader), which implement Google APIs

## Install

Via Composer

``` bash
$ composer require imonroe/cr_aspects_google
```

Add the following array to your `/config/services.php` file:
```
'google' => [
        'application_name' => env('GOOGLE_API_APP_NAME'),
        'client_id' => env('GOOGLE_API_CLIENT_ID'),
        'client_secret' => env('GOOGLE_API_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_API_REDIRECT'),
        'public_api_key' => env('GOOGLE_API_PUBLIC_API_KEY'),
        'auth_config_file' => env('GOOGLE_AUTH_CONFIG_FILE'),
        'custom_search_api_key' => env('GOOGLE_CUSTOM_SEARCH_API_KEY'),
        'custom_search_cz' => env('GOOGLE_CUSTOM_SEARCH_CZ'),
        'static_maps_api_key' => env('GOOGLE_STATIC_MAPS_API_KEY'),
    ],
```



Add the following to your .env file, along with the correct respective values:
```
GOOGLE_API_APP_NAME=YourAppName
GOOGLE_PROJECT_ID=yourProjectID
GOOGLE_API_CLIENT_ID=XXXX
GOOGLE_API_CLIENT_SECRET=XXX
GOOGLE_API_MAPS_API_KEY=
GOOGLE_API_PUBLIC_API_KEY=XXX
GOOGLE_API_REDIRECT=https://yoursite/auth/google/callback
GOOGLE_CUSTOM_SEARCH_API_KEY=XXXX
GOOGLE_CUSTOM_SEARCH_CZ=XXXX
GOOGLE_STATIC_MAPS_API_KEY=XXXX
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email ian@ianmonroe.com instead of using the issue tracker.

## Credits

- [Ian Monroe][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/imonroe/cr_aspects_google.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/imonroe/cr_aspects_google/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/imonroe/cr_aspects_google.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/imonroe/cr_aspects_google.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/imonroe/cr_aspects_google.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/imonroe/cr_aspects_google
[link-travis]: https://travis-ci.org/imonroe/cr_aspects_google
[link-scrutinizer]: https://scrutinizer-ci.com/g/imonroe/cr_aspects_google/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/imonroe/cr_aspects_google
[link-downloads]: https://packagist.org/packages/imonroe/cr_aspects_google
[link-author]: https://github.com/imonroe
[link-contributors]: ../../contributors
