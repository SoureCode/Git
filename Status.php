<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Component\Git;

use InvalidArgumentException;
use SoureCode\Component\Git\Filesystem\AbstractFile;
use SoureCode\Component\Git\Filesystem\FileInterface;
use SoureCode\Component\Git\Status\CopiedFile;
use SoureCode\Component\Git\Status\CopiedFileInterface;
use SoureCode\Component\Git\Status\IgnoredFile;
use SoureCode\Component\Git\Status\IgnoredFileInterface;
use SoureCode\Component\Git\Status\OrdinaryChangedAbstractFile;
use SoureCode\Component\Git\Status\OrdinaryChangedFileInterface;
use SoureCode\Component\Git\Status\RenamedFile;
use SoureCode\Component\Git\Status\RenamedFileInterface;
use SoureCode\Component\Git\Status\UnmergedFile;
use SoureCode\Component\Git\Status\UnmergedFileInterface;
use SoureCode\Component\Git\Status\UntrackedFile;
use SoureCode\Component\Git\Status\UntrackedFileInterface;
use Symfony\Component\String\AbstractString;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Status implements StatusInterface
{
    private const BRANCH_HEAD = 'branch\\.head (.*|\\(detached\\))$';
    private const BRANCH_OID = 'branch\\.oid ([A-Fa-f0-9]{40}|\\(initial\\))$';
    private const BRANCH_UPSTREAM = 'branch\\.upstream (.*)$';
    private const FILE_MODE = '(\\d{6})';
    private const HASH = '([A-Fa-f0-9]{40})';
    private const INDEX_WORK = '(?:([\\.MADRCU?!]{1})([\\.MADRCU?!]{1}))';
    private const PATH = '(.*)';
    private const PATH_WITH_SEPARATION = "(?:(.*)(?:\x09)(.*))";
    private const SCORE = '(?:(R|C)(\\d{1,3}))';
    private const SUBMODULE = '((?:N\\.\\.\\.)|(?:S(?:C|\\.)(?:M|\\.)(?:U|\\.)))';
    private RepositoryInterface $repository;
    /**
     * @var AbstractString[]
     */
    private array $lines;
    /**
     * @var FileInterface[]
     */
    private ?array $files = null;
    private ?string $branchObjectName = null;
    private ?string $branchHead = null;
    private ?string $branchUpstream = null;

    /**
     * @param AbstractString[] $lines
     */
    public function __construct(RepositoryInterface $repository, array $lines)
    {
        $this->repository = $repository;
        $this->lines = $lines;
    }

    public function getFiles(): array
    {
        $this->parse();

        return (array) $this->files;
    }

    private function parse(): void
    {
        if (null === $this->files) {
            foreach ($this->lines as $line) {
                if ($line->startsWith('#')) {
                    $this->parseHeader($line);
                } elseif ($line->startsWith('1')) {
                    $this->files[] = ($this->parseOrdinaryFile($line));
                } elseif ($line->startsWith('2')) {
                    $this->files[] = ($this->parseRenamedOrCopiedFile($line));
                } elseif ($line->startsWith('u')) {
                    $this->files[] = ($this->parseUnmergedFile($line));
                } elseif ($line->startsWith('?')) {
                    $this->files[] = ($this->parseUntrackedFile($line));
                } elseif ($line->startsWith('!')) {
                    $this->files[] = ($this->parseIgnoredFile($line));
                } else {
                    throw new InvalidArgumentException(sprintf('Invalid status line "%s"', (string) $line));
                }
            }
        }
    }

    private function parseHeader(AbstractString $line): void
    {
        if ($line->startsWith('# branch.oid')) {
            $this->branchObjectName = $this->parseBranchObjectName($line);
        } elseif ($line->startsWith('# branch.head')) {
            $this->branchHead = $this->parseBranchHead($line);
        } elseif ($line->startsWith('# branch.upstream')) {
            $this->branchUpstream = $this->parseBranchUpstream($line);
        } else {
            throw new InvalidArgumentException(sprintf('Invalid or unsupported status header line "%s"', (string) $line));
        }
    }

    private function parseBranchObjectName(AbstractString $line): string
    {
        $expression = self::getBranchOidExpression();

        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match($expression);

        // @codeCoverageIgnoreStart
        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse branch.oid header line.');
        }

        // @codeCoverageIgnoreEnd

        return $matches[1];
    }

    /**
     * Format: # branch.oid <commit> | (initial).
     */
    private static function getBranchOidExpression(): string
    {
        return '/# '.self::BRANCH_OID.'/';
    }

    private function parseBranchHead(AbstractString $line): string
    {
        $expression = self::getBranchHeadExpression();

        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match($expression);

        // @codeCoverageIgnoreStart
        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse branch.head header line.');
        }

        // @codeCoverageIgnoreEnd

        return $matches[1];
    }

    /**
     * Format: # branch.head <branch> | (detached).
     */
    private static function getBranchHeadExpression(): string
    {
        return '/# '.self::BRANCH_HEAD.'/';
    }

    private function parseBranchUpstream(AbstractString $line): string
    {
        $expression = self::getBranchUpstreamExpression();

        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match($expression);

        // @codeCoverageIgnoreStart
        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse branch.upstream header line.');
        }

        // @codeCoverageIgnoreEnd

        return $matches[1];
    }

    /**
     * Format: # branch.upstream <upstream_branch>.
     */
    private static function getBranchUpstreamExpression(): string
    {
        return '/# '.self::BRANCH_UPSTREAM.'/';
    }

    private function parseOrdinaryFile(AbstractString $line): OrdinaryChangedFileInterface
    {
        $expression = self::getOrdinaryExpression();

        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match($expression);

        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse ordinary status line.');
        }

        $file = $this->repository->resolveFile($matches[9]);

        return new OrdinaryChangedAbstractFile(
            $file->getPathname(),
            $file->getRelativePath(),
            $file->getRelativePathname(),
            $this->validateStatus($matches[1]),
            $this->validateStatus($matches[2]),
            $this->parseMode($matches[4]),
            $this->parseMode($matches[5]),
            $this->parseMode($matches[6]),
            $matches[7],
            $matches[8],
        );
    }

    /**
     * Format: 1 <XY> <sub> <mH> <mI> <mW> <hH> <hI> <path>.
     */
    private static function getOrdinaryExpression(): string
    {
        return '/'.implode(' ', [
                '1',
                self::INDEX_WORK,
                self::SUBMODULE,
                self::FILE_MODE,
                self::FILE_MODE,
                self::FILE_MODE,
                self::HASH,
                self::HASH,
                self::PATH,
            ]).'/';
    }

    protected function validateStatus(string $character): string
    {
        $states = [
            AbstractFile::STATUS_UNMODIFIED,
            AbstractFile::STATUS_MODIFIED,
            AbstractFile::STATUS_ADDED,
            AbstractFile::STATUS_DELETED,
            AbstractFile::STATUS_RENAMED,
            AbstractFile::STATUS_COPIED,
            AbstractFile::STATUS_UPDATED,
        ];

        if (\in_array($character, $states, true)) {
            return $character;
        }

        throw new InvalidArgumentException(sprintf('Invalid status character "%s".', $character));
    }

    private function parseMode(string $mode): int
    {
        return (int) $mode;
    }

    private function parseRenamedOrCopiedFile(AbstractString $line): RenamedFileInterface|CopiedFileInterface
    {
        $expression = self::getRenamedOrCopiedExpression();

        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match($expression);

        // @codeCoverageIgnoreStart
        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse renamed or copied status line.');
        }
        // @codeCoverageIgnoreEnd

        $file = $this->repository->resolveFile($matches[11]);
        $originalFile = $this->repository->resolveFile($matches[12]);

        if (AbstractFile::STATUS_RENAMED === $matches[1]) {
            return new RenamedFile(
                $file->getPathname(),
                $file->getRelativePath(),
                $file->getRelativePathname(),
                $originalFile->getPathname(),
                $originalFile->getRelativePath(),
                $originalFile->getRelativePathname(),
                $this->validateStatus($matches[1]),
                $this->validateStatus($matches[2]),
                $this->parseMode($matches[4]),
                $this->parseMode($matches[5]),
                $this->parseMode($matches[6]),
                $matches[7],
                $matches[8],
                (int) $matches[10],
            );
        }

        // @todo how to replicate this state?
        if (AbstractFile::STATUS_COPIED === $matches[1]) {
            return new CopiedFile(
                $file->getPathname(),
                $file->getRelativePath(),
                $file->getRelativePathname(),
                $originalFile->getPathname(),
                $originalFile->getRelativePath(),
                $originalFile->getRelativePathname(),
                $this->validateStatus($matches[1]),
                $this->validateStatus($matches[2]),
                $this->parseMode($matches[4]),
                $this->parseMode($matches[5]),
                $this->parseMode($matches[6]),
                $matches[7],
                $matches[8],
                (int) $matches[10],
            );
        }

        throw new InvalidArgumentException(sprintf('Invalid file status "%s".', $matches[3]));
    }

    /**
     * Format: 2 <XY> <sub> <mH> <mI> <mW> <hH> <hI> <X><score> <path><sep><origPath>.
     */
    private static function getRenamedOrCopiedExpression(): string
    {
        return '/'.implode(' ', [
                '2',
                self::INDEX_WORK,
                self::SUBMODULE,
                self::FILE_MODE,
                self::FILE_MODE,
                self::FILE_MODE,
                self::HASH,
                self::HASH,
                self::SCORE,
                self::PATH_WITH_SEPARATION,
            ]).'/';
    }

    private function parseUnmergedFile(AbstractString $line): UnmergedFileInterface
    {
        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match(self::getUnmergedExpression());

        // @codeCoverageIgnoreStart
        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse unmerged status line.');
        }
        // @codeCoverageIgnoreEnd

        $file = $this->repository->resolveFile($matches[11]);

        return new UnmergedFile(
            $file->getPathname(),
            $file->getRelativePath(),
            $file->getRelativePathname(),
            $this->validateStatus($matches[1]),
            $this->validateStatus($matches[2]),
            $this->parseMode($matches[4]),
            $this->parseMode($matches[5]),
            $this->parseMode($matches[6]),
            $this->parseMode($matches[7]),
            $matches[8],
            $matches[9],
            $matches[10],
        );
    }

    /**
     * Format: u <XY> <sub> <m1> <m2> <m3> <mW> <h1> <h2> <h3> <path>.
     */
    private static function getUnmergedExpression(): string
    {
        return '/'.implode(' ', [
                'u',
                self::INDEX_WORK,
                self::SUBMODULE,
                self::FILE_MODE,
                self::FILE_MODE,
                self::FILE_MODE,
                self::FILE_MODE,
                self::HASH,
                self::HASH,
                self::HASH,
                self::PATH,
            ]).'/';
    }

    private function parseUntrackedFile(AbstractString $line): UntrackedFileInterface
    {
        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match(self::getUntrackedExpression());

        // @codeCoverageIgnoreStart
        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse untracked status line.');
        }
        // @codeCoverageIgnoreEnd

        $file = $this->repository->resolveFile($matches[1]);

        return new UntrackedFile(
            $file->getPathname(),
            $file->getRelativePath(),
            $file->getRelativePathname(),
        );
    }

    /**
     * Format: ? <path>.
     */
    private static function getUntrackedExpression(): string
    {
        return '/'.implode(' ', [
                '\\?',
                self::PATH,
            ]).'/';
    }

    private function parseIgnoredFile(AbstractString $line): IgnoredFileInterface
    {
        /**
         * @var array<int, string> $matches
         */
        $matches = $line->match(self::getIgnoredExpression());

        // @codeCoverageIgnoreStart
        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse ignored status line.');
        }
        // @codeCoverageIgnoreEnd

        $file = $this->repository->resolveFile($matches[1]);

        return new IgnoredFile(
            $file->getPathname(),
            $file->getRelativePath(),
            $file->getRelativePathname(),
        );
    }

    /**
     * Format: ! <path>.
     */
    private static function getIgnoredExpression(): string
    {
        return '/'.implode(' ', [
                '!',
                self::PATH,
            ]).'/';
    }

    public function getBranchObjectName(): string
    {
        $this->parse();

        return (string) $this->branchObjectName;
    }

    public function getBranchHead(): string
    {
        $this->parse();

        return (string) $this->branchHead;
    }

    public function getBranchUpstream(): ?string
    {
        $this->parse();

        return $this->branchUpstream;
    }
}
