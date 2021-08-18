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

use DateTimeImmutable;
use Exception;
use JetBrains\PhpStorm\Pure;
use SoureCode\Component\Git\Exception\InvalidArgumentException;
use SoureCode\Component\Git\Filesystem\File;
use SoureCode\Component\Git\Filesystem\FileInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\String\AbstractString;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Repository implements RepositoryInterface
{
    public static string $binary = 'git';

    private string $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public static function init(string $directory, bool $bare = false): RepositoryInterface
    {
        self::executeGit(['init', '--bare' => $bare], $directory);

        return self::open($directory);
    }

    /**
     * {@inheritDoc}
     */
    public static function executeGit(
        array $parameters,
        string $directory,
        bool $autorun = true,
        string $input = null,
        array $environment = [],
    ): Process {
        $commandParameters = [];

        foreach ($parameters as $key => $parameter) {
            if (\is_string($key) && \is_bool($parameter)) {
                if ($parameter) {
                    $commandParameters[] = $key;
                }
            } elseif (\is_int($key) && \is_string($parameter)) {
                $commandParameters[] = $parameter;
            } else {
                throw new InvalidArgumentException(sprintf('Parameter ["%s" => "%s"] in argument \$parameter is invalid.', (string) $key, (string) $parameter));
            }
        }

        $process = new Process(array_merge([self::$binary], $commandParameters), $directory, $environment, $input);

        if ($autorun) {
            $process->mustRun();
        }

        return $process;
    }

    public static function open(string $directory): RepositoryInterface
    {
        $process = self::executeGit(['rev-parse', '--absolute-git-dir'], $directory);
        $result = new ProcessResult($process);
        $output = $result->getNormalizedOutput();
        $rootDirectory = \dirname((string) $output);

        return new self($rootDirectory);
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    #[Pure]
    public function getStage(): StageInterface
    {
        return new Stage($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus(): StatusInterface
    {
        $process = $this->execute(
            ['status', '--porcelain=2', '--branch', '--ignored', '--renames', '--untracked-files=all']
        );
        $result = new ProcessResult($process);

        return new Status($this, $result->getOutputLines());
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $parameters, string $input = null, array $environment = []): Process
    {
        return self::executeGit($parameters, $this->directory, true, $input, $environment);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveFile(string $relativePathname): FileInterface
    {
        $relativePath = \dirname($relativePathname);
        $absolutePath = $this->directory.'/'.$relativePathname;

        if ('.' === $relativePath) {
            $relativePath = '';
        }

        return new File($absolutePath, $relativePath, $relativePathname);
    }

    /**
     * {@inheritDoc}
     */
    public function getCommits(): array
    {
        $process = $this->execute(
            [
                'log',
                '--all',
                '--format=%H',
            ],
        );

        $result = new ProcessResult($process);
        $hashes = $result->getOutputLines();
        $commits = [];

        foreach ($hashes as $hash) {
            $commits[] = $this->getCommit((string) $hash);
        }

        return $commits;
    }

    /**
     * {@inheritDoc}
     */
    public function getCommit(string $object): CommitInterface
    {
        $format = implode('%x00%n%x00', [
            '%H', // hash
            '%an', // author name
            '%ae', // author email
            '%at', // author date, UNIX timestamp
            '%cn', // committer name
            '%ce', // committer email
            '%ct', // committer date, UNIX timestamp
            '%s', // subject
            '%b', // body
        ]);

        $process = $this->execute(
            [
                'show',
                '--no-patch',
                '--format='.$format,
                $object,
            ],
        );

        $result = new ProcessResult($process);
        $lines = $result->getNormalizedOutput()->split(\chr(0)."\n".\chr(0));

        $body = \array_key_exists(8, $lines) ? (string) $lines[8]->trim() : '';

        return new Commit(
            $this,
            new Hash((string) $lines[0]),
            new Person((string) $lines[1], (string) $lines[2]),
            new DateTimeImmutable('@'.(string) $lines[3]),
            new Person((string) $lines[4], (string) $lines[5]),
            new DateTimeImmutable('@'.(string) $lines[6]),
            new Message((string) $lines[7]->trim(), $body),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTag(string $name): TagInterface
    {
        $tags = $this->getTags();

        foreach ($tags as $tag) {
            if ($name === $tag->getShortName()) {
                return $tag;
            }
        }

        throw new Exception('Tag '.$name.' not found.');
    }

    /**
     * {@inheritDoc}
     */
    public function getTags(): array
    {
        $process = $this->execute(
            [
                'tag',
                '--format=%(refname)',
            ],
        );

        $result = new ProcessResult($process);
        $tagReferences = $result->getOutputLines();
        $tags = [];

        foreach ($tagReferences as $tagReference) {
            $path = ReferencePath::fromString((string) $tagReference);

            $tags[] = new Tag($this, $path);
        }

        return $tags;
    }

    /**
     * {@inheritDoc}
     */
    public function list(string $commit, bool $boundary = false, bool $merges = true): array
    {
        $process = $this->execute(
            [
                'rev-list',
                '--reverse',
                '--format=%H',
                '--no-merges' => !$merges,
                '--boundary' => $boundary,
                $commit,
            ],
        );

        $result = new ProcessResult($process);

        return array_reverse(
            array_map(
                fn (AbstractString $commit): CommitInterface => $this->getCommit((string) $commit),
                $result->getRevListOutput(),
            )
        );
    }

    public function getBranch(string $name): BranchInterface
    {
        $branches = $this->getBranches();

        foreach ($branches as $branch) {
            if ($name === $branch->getShortName()) {
                return $branch;
            }
        }

        throw new Exception('Branch '.$name.' not found.');
    }

    /**
     * {@inheritDoc}
     */
    public function getBranches(): array
    {
        $process = $this->execute(
            [
                'branch',
                '--format=%(refname)',
            ],
        );

        $result = new ProcessResult($process);
        $branchReferences = $result->getOutputLines();
        $branches = [];

        foreach ($branchReferences as $branchReference) {
            $path = ReferencePath::fromString((string) $branchReference);

            $branches[] = new Branch($this, $path);
        }

        return $branches;
    }
}
