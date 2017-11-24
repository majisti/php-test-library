<?php

declare(strict_types=1);

namespace Majisti\Testing\Utilities;

use LogicException;

class FriendlyPathBuilder
{
    const MAX_PATH_LENGTH = 250;

    /**
     * @var string
     */
    private $path;

    public function fromPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function buildDefaultFriendlyPath(string $path, $lengthThreshold = self::MAX_PATH_LENGTH): self
    {
        $this->fromPath($path)
            ->spacesToUnderscore()
            ->lineBreaksToUnderscore()
            ->camelCaseToSnakeCase()
            ->shortenFilenameForMaxPathSize($lengthThreshold)
        ;

        return $this;
    }

    public function camelCaseToSnakeCase(): self
    {
        //adapted solution from https://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
        $this->path = strtolower(preg_replace('/(?<!^)(?<!_)[A-Z]/', '_$0', $this->path));

        return $this;
    }

    public function spacesToUnderscore(): self
    {
        $this->path = str_replace(' ', '_', $this->path);

        return $this;
    }

    public function lineBreaksToUnderscore(): self
    {
        $this->path = preg_replace(['/\r\n/', '/\n/', '/\r/', sprintf('/%s/', PHP_EOL)], '_', $this->path);

        return $this;
    }

    public function toLowerCase(): self
    {
        $this->path = strtolower($this->path);

        return $this;
    }

    /**
     * Some examples of filename shortening:
     *  - /tmp/12345.txt This path length is 15. With a threshold of 13 you would get /tmp/123.txt
     *  - /tmp/12345.txt With a threshold of lower equals 10, this would throw a {@link LogicException}.
     *  - 12345 with a threshold of 3 will return 123.
     *
     * @throws LogicException If the truncated filename would lead to an unrealistic path name
     */
    public function shortenFilenameForMaxPathSize(int $lengthThreshold = self::MAX_PATH_LENGTH): self
    {
        $pathInfo = pathinfo($this->path);

        if (strlen($this->path) >= $lengthThreshold) { //ext4 and ntfs are limited to 255 characters
            $directoryNameLength = isset($pathInfo['dirname']) && '.' !== $pathInfo['dirname']
                ? strlen($pathInfo['dirname']) + 1 //let's not forget the ending slash
                : 0;
            $extensionLength = isset($pathInfo['extension'])
                ? strlen($pathInfo['extension']) + 1 //let's not forget the dot
                : 0;

            $newFilename = substr($pathInfo['filename'], 0, $lengthThreshold - $directoryNameLength - $extensionLength);

            if (!$newFilename) {
                throw new LogicException('Path threshold is too small, it would not yield a valid path');
            }

            $this->path = $newFilename;

            if ('.' !== $pathInfo['dirname']) {
                $this->path = $pathInfo['dirname'].'/'.$this->path;
            }

            if (isset($pathInfo['extension'])) {
                $this->path .= '.'.$pathInfo['extension'];
            }
        }

        return $this;
    }
}
