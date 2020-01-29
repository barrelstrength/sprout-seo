<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use yii\db\Query;

/**
 * m190222_000000_remove_slashes migration.
 */
class m190222_000000_remove_slashes extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function safeUp(): bool
    {
        // Get all of our Old and New Redirects
        $results = (new Query())
            ->select(['*'])
            ->from(['{{%sproutseo_redirects}} redirects'])
            ->all();

        foreach ($results as $result) {
            $this->db->createCommand()->update('{{%sproutseo_redirects}}', [
                // Remove any initial slashes from old and new redirects
                'oldUrl' => $this->removeSlash($result['oldUrl']),
                'newUrl' => $this->removeSlash($result['newUrl'])
            ],
                [
                    'id' => $result['id']
                ]
            )->execute();
        }

        return true;
    }

    public function removeSlash($uri)
    {
        $slash = '/';

        if (isset($uri[0]) && $uri[0] == $slash) {
            $uri = ltrim($uri, $slash);
        }

        return $uri;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190222_000000_remove_slashes cannot be reverted.\n";

        return false;
    }
}
