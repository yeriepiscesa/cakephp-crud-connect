<?php
declare(strict_types=1);

namespace CrudConnect\Test\TestCase\Navigation;

use CrudConnect\Navigation\FormRedirect;
use PHPUnit\Framework\TestCase;

class FormRedirectTest extends TestCase
{
    public function testListingReferrerKeepsQueryAndRejectsOtherDestinations(): void
    {
        $index = '/admin/business-users/tenants';
        self::assertSame($index . '?search=acme&page=2', FormRedirect::listingReferrer($index . '?search=acme&page=2', $index));
        self::assertNull(FormRedirect::listingReferrer('//example.com' . $index, $index));
        self::assertNull(FormRedirect::listingReferrer('https://example.com' . $index, $index));
        self::assertNull(FormRedirect::listingReferrer($index . '/edit/1', $index));
    }

    public function testSuccessTargetsAndInvalidOverrides(): void
    {
        $return = '/admin/business-users/tenants?search=acme&page=2';
        self::assertSame(['action' => 'edit', 42], FormRedirect::target('edit', 'stay', 42, $return));
        self::assertSame($return, FormRedirect::target('edit', 'index', 42, $return));
        self::assertSame(['action' => 'add'], FormRedirect::target('add', 'add', 42, $return));
        self::assertSame(['action' => 'edit', 42], FormRedirect::target('add', 'edit', 42, $return));
        self::assertSame($return, FormRedirect::target('add', 'index', 42, $return));
        self::assertSame(['action' => 'add'], FormRedirect::target('add', 'https://example.com', 42, $return));
        self::assertSame(['action' => 'index'], FormRedirect::target('add', 'edit', null, $return));
    }
}
