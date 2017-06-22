<?php

namespace Tests\Component;

use Tests\Utils\ComponentTest;

/**
 * This component test will verify that important listeners are registered with Symfony. Those not being
 * registered might break use cases and therefore we make sure that important ones are present.
 */
class EventListenersRegisteredTest extends ComponentTest
{
    /**
     * @var array
     *
     * This is where you can add your registered event listeners class list that you want to make sure are registered
     */
    private $listenersThatShouldBeRegistered = array(
        'kernel.request' => [],
    );

    private $listenerClassesFound;

    public function testThatImportantListenersAreRegistered()
    {
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        foreach ($this->listenersThatShouldBeRegistered as $eventName => $listenersThatShouldBeRegistered) {
            $listeners = $dispatcher->getListeners($eventName);

            if (empty($listenersThatShouldBeRegistered)) {
                $this->markTestSkipped('No listeners were registered for presence verification within the test.');
            }

            foreach ($listenersThatShouldBeRegistered as $class) {
                $this->listenerClassesFound = [];
                $listenerWasFound = $this->hasListenerClass($listeners, $class);

                $this->verifyThat(
                    sprintf(
                        "Listener {$class} should be present as a %s event, but it was not found.".str_repeat(PHP_EOL, 2).
                        '%s listener(s) were found. Here is the list: %s',
                        $eventName,
                        count($this->listenerClassesFound),
                        PHP_EOL.implode(','.PHP_EOL, $this->listenerClassesFound).PHP_EOL
                    ),
                    $listenerWasFound,
                    is(true)
                );
            }
        }
    }

    /**
     * @param array $listeners
     * @param string $class
     *
     * @return bool
     */
    private function hasListenerClass($listeners, $class)
    {
        foreach ($listeners as $listener) {
            $object = $listener[0];

            $this->listenerClassesFound[] = get_class($object);

            if ($object instanceof $class) {
                return true;
            }
        }

        return false;
    }
}
