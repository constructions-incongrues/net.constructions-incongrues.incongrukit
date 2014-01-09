<?php

namespace ConstructionsIncongrues\Incongrukit\Importer;

use ConstructionsIncongrues\Incongrukit\Collection\FileCollection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;

class CollectionImporter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $dirWorkspace;
    private $errors = array();

    public function __construct($dirWorkspace = null)
    {
        // Setup workspace
        if (is_null($dirWorkspace)) {
            $dirWorkspace = sys_get_temp_dir();
        }
        $this->dirWorkspace = $dirWorkspace;
        $fs = new Filesystem();
        $fs->mkdir($this->dirWorkspace);
    }

    public function import($directory, $dirDestinationRoot, FileCollection $collection)
    {

        // Create collection from archive contents in workspace
        $collection->buildFrom($directory);

        // Verify collection
        if (!$collection->verify()) {
            $this->errors = array_merge($this->errors, $collection->getErrors());
            $this->logger->error(
                'Collection verification failed',
                array('collection' => $collection->getId(), 'errors' => $collection->getErrors())
            );
            return false;
        } else {
            // Process collection
            $collection->process();
        }

        // Deploy files
        $collection->deploy($dirDestinationRoot);

        return true;
    }

    public function cleanup()
    {
        $fs = new Filesystem();
        $fs->remove($this->dirWorkspace);
        $this->logger->notice('[cleanup] Deleted workspace', array('directory' => $this->dirWorkspace));
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
