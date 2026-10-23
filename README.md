# Laravel Vault Cipher

![Laravel Vault Cipher tenant encryption banner](art/banner.png)

Encrypt strings and files with tenant-specific keys in Laravel applications.
You provide the key storage and lookup implementation; the package supplies
the encryption manager, streaming file support, and Eloquent casts.

[![Tests](https://github.com/eloquage/laravel-vault-cipher/actions/workflows/run-tests.yml/badge.svg)](https://github.com/eloquage/laravel-vault-cipher/actions/workflows/run-tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/illuma-law/laravel-vault-cipher)](https://packagist.org/packages/illuma-law/laravel-vault-cipher)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Requires PHP 8.3+ and Laravel 11, 12, or 13.

## Install

```bash
composer require illuma-law/laravel-vault-cipher
php artisan vendor:publish --tag=vault-cipher-config
```

## Configure a key provider

Implement `TenantKeyProvider` to look up each tenant's key from your secure
key store. This example assumes the key is encrypted in a `Tenant` model using
Laravel's application key; use a dedicated KMS or vault when appropriate.

```php
namespace App\Security;

use App\Models\Tenant;
use IllumaLaw\VaultCipher\Contracts\TenantKeyProvider;

final class DatabaseTenantKeyProvider implements TenantKeyProvider
{
    public function getKey(int|string $tenantId): string
    {
        $encryptedKey = Tenant::query()->findOrFail($tenantId)->encrypted_data_key;

        return decrypt($encryptedKey);
    }
}
```

Set the provider class in `config/vault-cipher.php`:

```php
'key_provider' => App\Security\DatabaseTenantKeyProvider::class,
```

Generate compatible keys with `VaultKeyGenerator::generate()` and store them
securely for their tenant.

## Encrypt strings and files

```php
use IllumaLaw\VaultCipher\Facades\TenantEncryptionManager;

$encrypted = TenantEncryptionManager::encryptString($tenantId, 'private text');
$plainText = TenantEncryptionManager::decryptString($tenantId, $encrypted);

TenantEncryptionManager::store($tenantId, 'contracts/agreement.pdf', $contents);
$contents = TenantEncryptionManager::get($tenantId, 'contracts/agreement.pdf');
```

File operations use the configured filesystem disk by default. You can pass a
disk explicitly or use `usingDecryptedTempPath()` to process a temporary
plaintext file that is cleaned up after the callback.

For Eloquent attributes, use `TenantEncrypted` or `TenantEncryptedArray` and
implement `TenantCipherable` to provide the model's tenant ID.

## Test

```bash
composer test
composer analyse
vendor/bin/pint --test
```

MIT licensed. See [LICENSE](LICENSE).
