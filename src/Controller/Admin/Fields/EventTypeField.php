<?php
declare(strict_types=1);

namespace App\Controller\Admin\Fields;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;


class EventTypeField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, ?string $label = null): EventTypeField
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)

            // this template is used in 'index' and 'detail' pages
            ->setTemplatePath('admin/field/event_type.html.twig');
    }
}
