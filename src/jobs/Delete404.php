<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\jobs;

use craft\queue\BaseJob;
use Craft;

use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\elements\Redirect;
use barrelstrength\sproutseo\enums\RedirectMethods;

/**
 * Delete404 job
 */
class Delete404 extends BaseJob
{
    public $totalToDelete;
    public $redirectIdToExclude;

    /**
     * Returns the default description for this job.
     *
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-seo', 'Deleting oldest 404 redirects');
    }

    /**
     * @param \craft\queue\QueueInterface|\yii\queue\Queue $queue
     *
     * @return bool
     * @throws \Throwable
     */
    public function execute($queue)
    {
        $params = [
            ':method' => RedirectMethods::PageNotFound
        ];

        $condition = 'method = :method';

        if ($this->redirectIdToExclude) {
            $condition .= ' and sproutseo_redirects.id != :redirectId';
            $params[':redirectId'] = $this->redirectIdToExclude;
        }

        $redirects = Redirect::find()
            ->where($condition, $params)
            ->limit($this->totalToDelete)
            ->orderBy(['dateUpdated' => SORT_ASC])
            ->all();

        $totalSteps = count($redirects);

        foreach ($redirects as $key => $redirect) {
            $step = $key + 1;
            $this->setProgress($queue, $step / $totalSteps);

            $response = Craft::$app->elements->deleteElementById($redirect->id);

            if (!$response) {
                SproutSeo::error('SproutSeo has failed to delete the 404 redirect Id:'.$redirect->id);
            }
        }

        return true;
    }
}