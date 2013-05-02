<?php

namespace Symfony\Cmf\Component\Testing\Functional;

use Symfony\Component\HttpKernel\KernelInterface;

class PhpcrOdmTestCase extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $session = $this->getContainer()->get('doctrine_phpcr.session');

        if ($session->nodeExists('/test')) {
            $session->getNode('/test')->remove();
        }

        if (!$session->nodeExists('/test')) {
            $session->getRootNode()->addNode('test', 'nt:unstructured');
        }

        $session->save();
    }

    public function getDm()
    {
        return $this->getContainer()->get('doctrine_phpcr.odm.document_manager');
    }
}
