<?php

namespace Mopa\Bundle\BootstrapBundle\Tests\Form;

class LegendLayoutTest extends AbstractDivLayoutTest
{
    public function testAsterisk()
    {
        $view = $this->factory
            ->createNamed('name', $this->getFormType('text'), null, array(
                'render_required_asterisk' => true,
            ))
            ->createView()
        ;
        $html = $this->renderLabel($view);
        $this->assertMatchesXpath($this->removeBreaks($html),
'
/label[@for="name"][@class=" control-label required"]
    [
        ./span[.="*"]
    ]
'
        );
    }

    public function testRenderFieldset()
    {
        $view = $this->factory->createNamedBuilder('name', $this->getFormType('form'))
            ->add('field1', $this->getFormType('text'))
            ->getForm()
            ->createView()
        ;

        $html = $this->renderForm($view);

        $this->assertMatchesXpath($html,
'
/form
    [
        ./fieldset
            [
                ./div[@class=" control-group"]
            ]
    ]
'
        );
    }

    public function testNoRenderFieldset()
    {
        $view = $this->factory->createNamedBuilder('name', $this->getFormType('form'), null, array('render_fieldset' => false))
            ->add('field1', $this->getFormType('text'))
            ->getForm()
            ->createView()
        ;

        $html = $this->renderForm($view);

        $this->assertMatchesXpath($html,
'
/form
    [
        ./div[@class=" control-group"]
    ]
'
        );
    }

    public function testRenderLegend()
    {
        $view = $this->factory->createNamedBuilder('name', $this->getFormType('form'))
            ->add('field1', $this->getFormType('text'))
            ->getForm()
            ->createView()
        ;

        $html = $this->renderForm($view);

        $this->assertMatchesXpath($html,
'
/form
    [
        ./fieldset
            [
                ./legend[.="[trans]Name[/trans]"]
                /following-sibling::div[@class=" control-group"]
            ]
    ]
'
        );
    }

    public function testNoRenderLegend()
    {
        $view = $this->factory->createNamedBuilder('name', $this->getFormType('form'), null, array('show_legend' => false))
            ->add('field1', $this->getFormType('text'))
            ->getForm()
            ->createView()
        ;

        $html = $this->renderForm($view);

        $this->assertMatchesXpath($html,
'
/form
    [
        ./fieldset
            [
                not(./legend)
            ]
    ]
'
        );
    }

    public function testLabelRender()
    {
        $view = $this->factory
            ->createNamed('name', $this->getFormType('text'), null, array(
                'label_render' => false,
            ))
            ->createView()
        ;
        $html = $this->renderRow($view);
        $this->assertMatchesXpath($this->removeBreaks($html),
'
/div[@class=" control-group"]
    [
        not(./label)
    ]
'
        );
    }
}
