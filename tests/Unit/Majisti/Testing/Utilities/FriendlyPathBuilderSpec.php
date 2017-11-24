<?php

declare(strict_types=1);

namespace Unit\Majisti\Testing\Utilities;

use PhpSpec\ObjectBehavior;

class FriendlyPathBuilderSpec extends ObjectBehavior
{
    public function it_converts_spaces_to_underscore()
    {
        $this->fromPath('foo bar baz')
            ->spacesToUnderscore()
            ->getPath()
            ->shouldBe('foo_bar_baz');
    }

    public function it_should_convert_camel_case_to_snake_case(): void
    {
        $this
            ->fromPath('fooBarBaz_bar')
            ->camelCaseToSnakeCase()
            ->getPath()
            ->shouldEqual('foo_bar_baz_bar');
    }

    public function it_can_lowercase()
    {
        $this
            ->fromPath('FOObarBAZ')
            ->toLowerCase()
            ->getPath()
            ->shouldEqual('foobarbaz');
    }

    public function it_converts_line_breaks_to_underscore()
    {
        $this->fromPath(<<<EOF
A\rpath\nthat
is\r\non
multiple lines
EOF
        )
            ->lineBreaksToUnderscore()
            ->getPath()
            ->shouldEqual('A_path_that_is_on_multiple lines');
    }

    public function it_truncates_scalar_strings(): void
    {
        $this
            ->buildDefaultFriendlyPath('12345', 3)
            ->getPath()
            ->shouldBe('123');
    }

    public function it_should_truncate_before_extension(): void
    {
        $this
            ->buildDefaultFriendlyPath('fooooo.txt', 5)
            ->getPath()
            ->shouldBe('f.txt');
    }

    public function it_keeps_full_path_when_truncating(): void
    {
        $this
            ->buildDefaultFriendlyPath('C:/foo/bar/12345.txt', 18)
            ->getPath()
            ->shouldBe('c:/foo/bar/123.txt');
    }

    public function it_should_throw_exception_if_path_truncation_threshold_is_unrealistic(): void
    {
        $this
            ->shouldThrow(\LogicException::class)
            ->during('buildDefaultFriendlyPath', ['f.txt', 1]);
    }

    public function it_should_convert_path_to_a_default_friendly_path_name(): void
    {
        $convertedPath = $this->buildDefaultFriendlyPath(<<<EOF
A\rweird\nbackup
name\r\non
MultipleLines
that is waaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaay
and waaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaay
and will definitely pass by the 255 character mark here
too long and should be truncated
EOF
        )->getPath();

        $convertedPath->shouldContain('a_weird_backup_name_on_multiple_lines');
        $convertedPath->shouldNotContain('here');
    }
}
