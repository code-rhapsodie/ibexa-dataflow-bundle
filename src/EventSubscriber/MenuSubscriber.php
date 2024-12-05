<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\EventSubscriber;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [ConfigureMenuEvent::MAIN_MENU => 'onConfigureMenu'];
    }

    public function onConfigureMenu(ConfigureMenuEvent $event)
    {
        /** @var \Knp\Menu\ItemInterface $menu */
        $menu = $event->getMenu();
        if (!isset($menu[MainMenuBuilder::ITEM_ADMIN])) {
            return;
        }

        $menu[MainMenuBuilder::ITEM_ADMIN]->addChild(
            'ibexa_dataflow_dashboard',
            [
                'label' => 'coderhapsodie.ibexa_dataflow',
                'route' => 'coderhapsodie.ibexa_dataflow.main',
            ]
        );
    }
}
