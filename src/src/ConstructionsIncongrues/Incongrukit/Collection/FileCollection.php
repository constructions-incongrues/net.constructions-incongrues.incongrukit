<?php

namespace ConstructionsIncongrues\Incongrukit\Collection;

use ConstructionsIncongrues\Collection\Exception\InvalidCollectionException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Finder\Finder;

class FileCollection implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $errors = array();
    protected $eventDispatcher;
    protected $id;
    protected $groups = array();
    protected $groupDefaults = array(
        'countMax'     => null,
        'countMin'     => null,
        'files'        => null,
        'finder'       => null,
        'patternFile'  => null,
        'patternGroup' => null
    );
    protected $parameters = array();
    protected $path;

    public function __construct(array $parameters = array(), $id = null)
    {
        // Setup collection id
        if (is_null($id)) {
            $id = uniqid();
        }
        $this->id = $id;

        // Store parameters
        $this->parameters = $parameters;

        // Setup default logger
        $this->setLogger(new NullLogger());
    }

    public function buildFrom($path)
    {
        // Store path
        $this->path = $path;

        // Configure collection
        $this->configure();
    }

    protected function configure()
    {
    }

    protected function addGroup($name, $spec)
    {
        // Merge group spec with defaults
        $this->groups[$name] = array_merge($this->groupDefaults, $spec);

        // Create group finder
        $finder = new Finder();
        $finder->name($spec['patternGroup'])->in($this->path);
        $this->groups[$name]['files'] = iterator_to_array($finder->getIterator(), false);

        // Log
        $this->logger->info(
            'Added group to collection',
            array('collection' => $this->id, 'group' => $name, 'numFiles' => count($this->groups[$name]['files']))
        );
    }

    public function getGroup($name)
    {
        if (!isset($this->groups[$name])) {
            throw new \InvalidArgumentException(
                sprintf("Group does not exist %s", json_encode(array('group' => $name), JSON_UNESCAPED_SLASHES))
            );
        }

        return $this->groups[$name];
    }

    public function verify()
    {
        // Log
        $this->logger->notice(
            '[verification] Collection verification started',
            array('collection' => $this->getId())
        );

        // Verify each group
        foreach ($this->groups as $name => $spec) {
            // Expected minimum number of files
            if (!is_null($spec['countMin'])) {
                if (count($spec['files']) < $spec['countMin']) {
                    $this->errors[] = sprintf(
                        'Number of found files lower than expected minimum %s',
                        json_encode(
                            array(
                                'countFound'   => count($spec['files']),
                                'countMin'     => $spec['countMin'],
                                'path'         => $this->path,
                                'patternGroup' => $spec['patternGroup']
                            ),
                            JSON_UNESCAPED_SLASHES
                        )
                    );
                }
            }

            // Expected maximum number of files
            if (!is_null($spec['countMax'])) {
                if (count($spec['files']) > $spec['countMax']) {
                    $this->errors[] = sprintf(
                        'Number of found files higher than expected minimum %s',
                        json_encode(
                            array(
                                'countFound'   => count($spec['files']),
                                'countMax'     => $spec['countMax'],
                                'path'         => $this->path,
                                'patternGroup' => $spec['patternGroup']
                            ),
                            JSON_UNESCAPED_SLASHES
                        )
                    );
                }
            }

            // Group files name pattern
            if ($spec['patternFile']) {
                foreach ($spec['files'] as $file) {
                    if (!preg_match($spec['patternFile'], $file->getFilename())) {
                        $this->errors[] = sprintf(
                            'Invalid file name in group %s',
                            json_encode(
                                array(
                                    'filename'    => $file->getFilename(),
                                    'group'       => $name,
                                    'patternFile' => $spec['patternFile']
                                ),
                                JSON_UNESCAPED_SLASHES
                            )
                        );
                    }
                }
            }
        }

        // Return encountered errors
        if (count($this->errors)) {
            // Log
            $this->logger->error(
                '[verification] Collection verification failed',
                array('collection' => $this->getId(), 'errors' => $this->getErrors())
            );
            return false;
        }

        // Log
        $this->logger->notice(
            '[verification] Collection verification succeeded',
            array('collection' => $this->getId())
        );

        return true;

    }

    public function process()
    {
        $this->logger->notice('[processing] Collection processing started', array('collection' => $this->getId()));
    }

    public function deploy($destination)
    {
        $this->logger->notice(
            '[deploy] Collection deployment started',
            array('collection' => $this->getId(), 'destination' => $destination)
        );
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getId()
    {
        return $this->id;
    }
}
