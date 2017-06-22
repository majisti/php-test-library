<?php

namespace Tests\Component;

use Tests\Utils\ComponentTest;

class TwitterTest extends ComponentTest
{
    public function testShouldFetchFeedsFromSertiStatusPage()
    {
        $twitterApi = $this->getContainer()->get('app.twitter.api');
        $feeds = $twitterApi->getLatestStatusesFromTimeline(1);

        $this->verifyThat(count($feeds), is(1));
        $this->verifyThat($feeds[0]->getUrl(), is(notNullValue()));
        $this->verifyThat($feeds[0]->getContent(), is(notNullValue()));
        $this->verifyThat($feeds[0]->getPostedAt(), is(notNullValue()));
    }
}