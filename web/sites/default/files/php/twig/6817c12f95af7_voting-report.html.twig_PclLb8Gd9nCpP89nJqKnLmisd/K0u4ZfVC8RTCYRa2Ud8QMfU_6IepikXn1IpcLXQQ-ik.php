<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* modules/custom/voting_system/templates/voting-report.html.twig */
class __TwigTemplate_0a84817216b252d294b9a21ec94c03aa extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 10
        yield "
<div class=\"voting-report\">
  ";
        // line 12
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["questions"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["question"]) {
            // line 13
            yield "    <div class=\"voting-report-question\">
      <h2>";
            // line 14
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["question"], "label", [], "any", false, false, true, 14), "html", null, true);
            yield "</h2>
      <div class=\"voting-report-total\">
        ";
            // line 16
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Total de votos: @total", ["@total" => CoreExtension::getAttribute($this->env, $this->source, $context["question"], "total_votes", [], "any", false, false, true, 16)]));
            yield "
      </div>

      <div class=\"voting-report-options\">
        ";
            // line 20
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["question"], "options", [], "any", false, false, true, 20));
            foreach ($context['_seq'] as $context["_key"] => $context["option"]) {
                // line 21
                yield "          <div class=\"voting-report-option\">
            <div class=\"voting-report-option-header\">
              <div class=\"voting-report-option-label\">";
                // line 23
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["option"], "label", [], "any", false, false, true, 23), "html", null, true);
                yield "</div>
              <div class=\"voting-report-option-count\">
                ";
                // line 25
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["option"], "votes", [], "any", false, false, true, 25), "html", null, true);
                yield " ";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("votos"));
                yield " (";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["option"], "percentage", [], "any", false, false, true, 25), "html", null, true);
                yield "%)
              </div>
            </div>
            <div class=\"voting-report-option-bar-container\">
              <div class=\"voting-report-option-bar\" style=\"width: ";
                // line 29
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["option"], "percentage", [], "any", false, false, true, 29), "html", null, true);
                yield "%\"></div>
            </div>
          </div>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['option'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 33
            yield "      </div>
    </div>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['question'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 36
        yield "</div> ";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["questions"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/voting_system/templates/voting-report.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  109 => 36,  101 => 33,  91 => 29,  80 => 25,  75 => 23,  71 => 21,  67 => 20,  60 => 16,  55 => 14,  52 => 13,  48 => 12,  44 => 10,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/voting_system/templates/voting-report.html.twig", "/app/web/modules/custom/voting_system/templates/voting-report.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 12];
        static $filters = ["escape" => 14, "t" => 16];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['for'],
                ['escape', 't'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
