<?php

namespace Tests\Component\Repository;

use AppBundle\Entity\Repository\TwitterFeedRepository;
use AppBundle\Entity\TwitterFeed;

/**
 * @method TwitterFeedRepository uut()
 */
class TwitterFeedRepositoryTest extends RepositoryTest
{
    public function testCanGetLastRequestedFeed()
    {
        $repo = $this->getRepository();
        $feed = $repo->getLastRequestedFeed();

        $this->verifyThat($feed, is(notNullValue()));
    }

    public function testCanRefreshFeeds()
    {
        $feeds = [
            (new TwitterFeed())
                ->setUrl('http://foo.com')
                ->setRequestedAt(new \DateTime())
                ->setPostedAt(new \DateTime())
                ->setContent("foo")
        ];

        $repo = $this->getRepository();

        $this->verifyThat(count($repo->findAll()), is(greaterThan(1))); //we already have fixtures loaded
        $repo->refreshFeeds($feeds);
        $this->verifyThat(count($repo->findAll()), is(1));
    }

    /**
     * @return TwitterFeedRepository
     */
    protected function getRepository()
    {
        $repo = $this->entityManager->getRepository('AppBundle:TwitterFeed');
        return $repo;
    }
}
