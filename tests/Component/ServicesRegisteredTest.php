<?php

namespace Tests\Component;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Tests\Utils\ComponentTest;

/**
 * Making sure that custom services are integrated into the application. Each services should be
 * unit tested in isolation. We only need to test the integration with the application here.
 *
 * You do not need to add every services here, only crucial ones that could break the entire application.
 */
class ServicesRegisteredTest extends ComponentTest
{
    private $servicesToVerifyAvailability = [
        'app.markdown.parser',
    ];

    public function testServicesAreRegisteredCorrectly()
    {
        if (empty($this->servicesToVerifyAvailability)) {
            $this->markTestSkipped('No services were registered for presence verification within the test.');
        }

        foreach ($this->servicesToVerifyAvailability as $serviceId) {
            try {
                $service = $this->getContainer()->get($serviceId);
                $this->verifyThat($service, is(notNullValue()));
            } catch (ServiceNotFoundException $e) {
                $this->fail(sprintf('%s service is not available, but it should be.', $serviceId));
            }
        }
    }
}
