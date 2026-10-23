<?php

declare(strict_types=1);

use IllumaLaw\VaultCipher\Support\NormalizesEncryptionKeys;

final class EncryptionKeyNormalizer
{
    use NormalizesEncryptionKeys;

    public function decode(string $key): string
    {
        return $this->decodeBase64Key($key);
    }
}

it('decodes a base64-encoded AES-256 key', function () {
    $key = random_bytes(32);

    expect((new EncryptionKeyNormalizer)->decode(base64_encode($key)))->toBe($key);
});

it('rejects invalid encryption key encodings', function () {
    expect(fn () => (new EncryptionKeyNormalizer)->decode('invalid-key'))
        ->toThrow(RuntimeException::class, 'Invalid encryption key format.');
});
