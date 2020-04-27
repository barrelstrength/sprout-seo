<?php

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m200307_000000_cleanup_optimized_entryelement_metadata_values extends Migration
{
    /**
     * @return bool
     * @noinspection ClassConstantCanBeUsedInspection
     */
    public function safeUp(): bool
    {
        $elementMetadataFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from('{{%fields}} as fields')
            ->where([
                '[[fields.type]]' => 'barrelstrength\sproutseo\fields\ElementMetadata'
            ])
            ->indexBy('id')
            ->all();

        foreach ($elementMetadataFields as $elementMetadataFieldId => $elementMetadataField) {

            $elementMetadataSettings = json_decode($elementMetadataField['settings'], true);

            if (!is_array($elementMetadataSettings)) {
                continue;
            }

            $optimizedTitle = $elementMetadataSettings['optimizedTitleField'] ?? null;
            $optimizedDescription = $elementMetadataSettings['optimizedDescriptionField'] ?? null;
            $optimizedImage = $elementMetadataSettings['optimizedImageField'] ?? null;
            $optimizedKeywords = $elementMetadataSettings['optimizedKeywordsField'] ?? null;
            $canonical = $elementMetadataSettings['editCanonical'] ?? null;

            $optimizedTitleManually = $optimizedTitle === 'manually';
            $optimizedDescriptionManually = $optimizedDescription === 'manually';
            $optimizedImageManually = $optimizedImage === 'manually';
            $optimizedKeywordsManually = $optimizedKeywords === 'manually';
            $canonicalEditable = $canonical === '1';

            $fieldLayoutIdsWithElementMetadataFields = (new Query())
                ->select(['layoutId'])
                ->from('{{%fieldlayoutfields}} as fieldlayoutfields')
                ->where([
                    '[[fieldlayoutfields.fieldId]]' => $elementMetadataFieldId
                ])
                ->distinct()
                ->column();

            $sectionIdsWithElementMetadataFields = (new Query())
                ->select(['entrytypes.sectionId'])
                ->from('{{%entrytypes}} as entrytypes')
                ->where([
                    'in',
                    '[[entrytypes.fieldLayoutId]]',
                    $fieldLayoutIdsWithElementMetadataFields
                ])
                ->column();

            foreach ($sectionIdsWithElementMetadataFields as $sectionId) {

                $fieldHandle = 'field_'.$elementMetadataField['handle'];

                $metaWithOptimizedValues = (new Query())
                    ->select(['content.id', 'content.'.$fieldHandle])
                    ->from('{{%content}} as content')
                    ->leftJoin('{{%entries}} as entries', '[[content.elementId]] = [[entries.id]]')
                    ->where([
                        '[[entries.sectionId]]' => $sectionId
                    ])
                    ->andWhere([
                        'or not like',
                        "[[content.$fieldHandle]]",
                        [
                            '"optimizedTitle":null',
                            '"optimizedDescription":null',
                            '"optimizedImage":null',
                            '"optimizedKeywords":null',
                            '"canonical":null'
                        ]
                    ])
                    ->all();

                foreach ($metaWithOptimizedValues as $row) {
                    $rowId = $row['id'];
                    $oldFieldSettings = json_decode($row[$fieldHandle], true);
                    $newFieldSettings = $oldFieldSettings;

                    $optimizedTitleValue = $oldFieldSettings['optimizedTitle'] ?? null;
                    $optimizedDescriptionValue = $oldFieldSettings['optimizedDescription'] ?? null;
                    $optimizedImageValue = $oldFieldSettings['optimizedImage'] ?? null;
                    $optimizedKeywordsValue = $oldFieldSettings['optimizedKeywords'] ?? null;
                    $canonicalValue = $oldFieldSettings['canonical'] ?? null;

                    $newFieldSettings['optimizedTitle'] =
                        $optimizedTitleManually ? $optimizedTitleValue : null;
                    $newFieldSettings['optimizedDescription'] =
                        $optimizedDescriptionManually ? $optimizedDescriptionValue : null;
                    $newFieldSettings['optimizedImage'] =
                        $optimizedImageManually ? $optimizedImageValue : null;
                    $newFieldSettings['optimizedKeywords'] =
                        $optimizedKeywordsManually ? $optimizedKeywordsValue : null;
                    $newFieldSettings['canonical'] = $canonicalEditable ? $canonicalValue : null;

                    // Try to clean up other potential scenarios
                    if ($newFieldSettings['canonical'] !== null) {
                        // Remove canonical values that include an action URL
                        if (strpos($newFieldSettings['canonical'], 'actions/queue/run') !== false) {
                            $newFieldSettings['canonical'] = null;
                        }
                    }

                    if (!is_array($newFieldSettings)) {
                        continue;
                    }

                    $this->update('{{%content}}', [
                        $fieldHandle => json_encode($newFieldSettings)
                    ], [
                        'id' => $rowId
                    ], [], false);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200307_000000_cleanup_optimized_entryelement_metadata_values cannot be reverted.\n";

        return false;
    }
}
