<?php

namespace Mopa\Bundle\BootstrapBundle\Tests\Form;

use Mopa\Bundle\BootstrapBundle\Form\Extension\EmbedFormExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\ErrorTypeFormTypeExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\HelpFormTypeExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\IconButtonExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\LayoutFormTypeExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\LegendFormTypeExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\StaticTextExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\TabbedFormTypeExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\WidgetCollectionFormTypeExtension;
use Mopa\Bundle\BootstrapBundle\Form\Extension\WidgetFormTypeExtension;
use Mopa\Bundle\BootstrapBundle\Form\Type\TabType;
use Mopa\Bundle\BootstrapBundle\Twig\IconExtension;
use Mopa\Bundle\BootstrapBundle\Twig\MopaBootstrapInitializrTwigExtension;
use Mopa\Bundle\BootstrapBundle\Twig\MopaBootstrapTwigExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Tests\Extension\Fixtures\StubTranslator;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\HttpKernel\Kernel;
use Twig_Environment;

abstract class AbstractDivLayoutTest extends FormIntegrationTestCase
{
    protected $extension;
    protected $tabFactory;
    protected $formTypeMap = array(
        'form' => 'Symfony\Component\Form\Extension\Core\Type\FormType',
        'text' => 'Symfony\Component\Form\Extension\Core\Type\TextType',
        'email' => 'Symfony\Component\Form\Extension\Core\Type\EmailType',
    );

    /**
     * @throws \Twig_Error_Loader
     */
    protected function setUp()
    {
        // Setup factory for tabs
        $this->tabFactory = Forms::createFormFactory();

        parent::setUp();

        $reflectionClass = class_exists('Symfony\Bridge\Twig\Form\TwigRenderer') ? 'Symfony\Bridge\Twig\Form\TwigRenderer' : 'Symfony\Bridge\Twig\Form\TwigRendererEngine';
        $reflection = new \ReflectionClass($reflectionClass);
        $bridgeDirectory = dirname($reflection->getFileName()).'/../Resources/views/Form';

        $loader = new \Twig_Loader_Filesystem([
            $bridgeDirectory,
            __DIR__.'/../../Resources/views/Form',
        ]);

        $loader->addPath(__DIR__.'/../../Resources/views', 'MopaBootstrap');

        $this->environment = new \Twig_Environment($loader, ['strict_variables' => true]);
        $this->environment->addExtension(new TranslationExtension(new StubTranslator()));
        $this->environment->addGlobal('global', '');

        $this->rendererEngine = new TwigRendererEngine([
            'form_div_layout.html.twig',
            'fields.html.twig',
        ], $this->environment);

        if (version_compare(Kernel::VERSION, '3.0.0', '<')) {
            $this->setUpVersion2();
        } else {
            $this->setUpVersion3Plus();
        }
    }

    private function setUpVersion2()
    {
        $csrfProvider = $this->getMockBuilder('Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface')->getMock();
        $this->renderer = new TwigRenderer($this->rendererEngine, $csrfProvider);
        $this->environment->addExtension($this->extension = new FormExtension($this->renderer));
        $this->extension->initRuntime($this->environment);
    }

    private function setUpVersion3Plus()
    {
        $csrfProvider = $this->getMockBuilder('Symfony\Component\Security\Csrf\CsrfTokenManagerInterface')->getMock();
        $loaders = [
            'Symfony\Component\Form\FormRenderer' => function () use ($csrfProvider) {
                return new FormRenderer($this->rendererEngine, $csrfProvider);
            },
        ];

        $runtime = 'Symfony\Component\Form\FormRenderer';

        if (class_exists('Symfony\Bridge\Twig\Form\TwigRenderer')) {
            $loaders['Symfony\Bridge\Twig\Form\TwigRenderer'] = function () use ($csrfProvider) {
                return new TwigRenderer($this->rendererEngine, $csrfProvider);
            };

            $runtime = 'Symfony\Bridge\Twig\Form\TwigRenderer';
        }

        // Add runtime loader
        $this->environment->addRuntimeLoader(new \Twig_FactoryRuntimeLoader($loaders));
        $this->renderer = $this->environment->getRuntime($runtime);

        $this->extension = new FormExtension();
        $this->extension->renderer = $this->renderer;

        $this->environment->addExtension($this->extension);
    }

    /**
     * @return PreloadedExtension[]
     */
    protected function getExtensions()
    {
        return array(new PreloadedExtension(array(), array(
            $this->getFormType('form') => array(
                $this->getHelpFormTypeExtension(),
                $this->getWidgetFormTypeExtension(),
                $this->getWidgetCollectionFormTypeExtension(),
                $this->getLegendFormTypeExtension(),
                $this->getErrorTypeFormTypeExtension(),
                $this->getIconButtonExtension(),
            ),
            $this->getFormType('text') => array(
            ),
        )));
    }

    /**
     * @return HelpFormTypeExtension
     */
    protected function getHelpFormTypeExtension()
    {
        $popoverOptions = array(
            'title' => null,
            'content' => null,
            'text' => null,
            'trigger' => 'hover',
            'toggle' => 'popover',
            'icon' => 'info-sign',
            'placement' => 'right',
            'selector' => null,
        );

        $tooltipOptions = array(
            'title' => null,
            'text' => null,
            'icon' => 'info-sign',
            'placement' => 'top',
        );

        return new HelpFormTypeExtension(array(
            'help_inline' => null,
            'help_block' => null,
            'help_label' => null,
            'help_block_popover' => $popoverOptions,
            'help_label_popover' => $popoverOptions,
            'help_widget_popover' => $popoverOptions,
            'help_block_tooltip' => $tooltipOptions,
            'help_label_tooltip' => $tooltipOptions,
        ));
    }

    /**
     * @return WidgetFormTypeExtension
     */
    protected function getWidgetFormTypeExtension()
    {
        return new WidgetFormTypeExtension(array(
            'checkbox_label' => 'both',
        ));
    }

    /**
     * @return WidgetFormTypeExtension
     */
    protected function getWidgetCollectionFormTypeExtension()
    {
        return new WidgetCollectionFormTypeExtension(array(
            'render_collection_item' => null,
        ));
    }

    /**
     * @return LegendFormTypeExtension
     */
    protected function getLegendFormTypeExtension()
    {
        return new LegendFormTypeExtension(array(
            'render_fieldset' => true,
            'show_legend' => true,
            'show_child_legend' => false,
            'errors_on_forms' => false,
            'render_required_asterisk' => false,
            'render_optional_text' => true,
        ));
    }

    /**
     * @return ErrorTypeFormTypeExtension
     */
    protected function getErrorTypeFormTypeExtension()
    {
        return new ErrorTypeFormTypeExtension(array(
            'error_type' => null,
        ));
    }

    /**
     * @return TabbedFormTypeExtension
     */
    protected function getTabbedFormTypeExtension()
    {
        return new TabbedFormTypeExtension($this->tabFactory, array(
            'class' => 'tabs nav-tabs',
        ));
    }

    /**
     * @return TabbedFormTypeExtension
     */
    protected function getIconButtonExtension()
    {
        return new IconButtonExtension(array(
            'icon' => null,
            'icon_color' => null,
        ));
    }

    /**
     * @param string $html
     * @param string $expression
     * @param int    $count
     */
    protected function assertMatchesXpath($html, $expression, $count = 1)
    {
        $dom = new \DomDocument('UTF-8');
        try {
            // Wrap in <root> node so we can load HTML with multiple tags at
            // the top level
            $dom->loadXml('<root>'.$html.'</root>');
        } catch (\Exception $e) {
            $this->fail(sprintf(
                "Failed loading HTML:\n\n%s\n\nError: %s",
                $html,
                $e->getMessage()
            ));
        }
        $xpath = new \DOMXPath($dom);
        $nodeList = $xpath->evaluate('/root'.$expression);
        if ($nodeList->length != $count) {
            $dom->formatOutput = true;
            $this->fail(sprintf(
                "Failed asserting that \n\n%s\n\nmatches exactly %s. Matches %s in \n\n%s",
                $expression,
                $count == 1 ? 'once' : $count.' times',
                $nodeList->length == 1 ? 'once' : $nodeList->length.' times',
                // strip away <root> and </root>
                substr($dom->saveHTML(), 6, -8)
            ));
        }
    }

    /**
     * @param string $html
     *
     * @return string
     */
    protected function removeBreaks($html)
    {
        return str_replace('&nbsp;', '', $html);
    }

    /**
     * @param FormView $view
     * @param array    $vars
     *
     * @return string
     */
    protected function renderForm(FormView $view, array $vars = array())
    {
        return (string) $this->extension->renderer->renderBlock($view, 'form', $vars);
    }

    /**
     * @param FormView $view
     * @param array    $vars
     *
     * @return string
     */
    protected function renderRow(FormView $view, array $vars = array())
    {
        return (string) $this->extension->renderer->searchAndRenderBlock($view, 'row', $vars);
    }

    /**
     * @param FormView $view
     * @param array    $vars
     *
     * @return string
     */
    protected function renderWidget(FormView $view, array $vars = array())
    {
        return (string) $this->extension->renderer->searchAndRenderBlock($view, 'widget', $vars);
    }

    /**
     * @param FormView $view
     * @param string   $label
     * @param array    $vars
     *
     * @return string
     */
    protected function renderLabel(FormView $view, $label = null, array $vars = array())
    {
        if ($label !== null) {
            $vars += array('label' => $label);
        }

        return (string) $this->extension->renderer->searchAndRenderBlock($view, 'label', $vars);
    }

    protected function getFormType($name)
    {
         if(method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
             return $this->formTypeMap[$name];
         }

         return $name;
    }
}
