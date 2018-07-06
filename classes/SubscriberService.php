<?php namespace indikator\news\classes;

use Indikator\News\Models\Categories;
use Db;
use Indikator\News\Models\Settings;

trait SubscriberService
{

    /**
     * Handles subscriber registration
     * either by registration in the frontend or by creating in the backend
     * @param $listOfCategoryIds array of subscribing Ids
     */
    public function onSubscriberRegister($subscriber, $listOfCategoryIds = []) {

        // Register category
        foreach ($listOfCategoryIds as $category) {
            if (is_numeric($category) && Categories::where(['id' => $category, 'hidden' => 2])->count() == 1 && Db::table('indikator_news_relations')->where(['subscriber_id' => $subscriber->id, 'categories_id' => $listOfCategoryIds])->count() == 0) {
                Db::table('indikator_news_relations')->insertGetId([
                    'subscriber_id' => $subscriber->id,
                    'categories_id' => $category
                ]);
            }
        }

        if (! $subscriber->isActive()) {

            if(Settings::get('newsletter_double_opt_in', true))
            {
                $subscriber->register();
                ConfirmationHandler::sendConfirmationEmailToSubscriber($subscriber);
            } else {
                $subscriber->activate();
            }

        }
    }


}