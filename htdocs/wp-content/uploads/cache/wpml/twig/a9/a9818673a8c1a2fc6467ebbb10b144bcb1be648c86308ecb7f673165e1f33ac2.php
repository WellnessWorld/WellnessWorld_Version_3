<?php

/* settings-ui.twig */
class __TwigTemplate_161528b38af42c876e0f64f4e0e395708e7758af0966760c8644077730061d3b extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<form method=\"post\" action=\"";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "action", array()), "html", null, true);
        echo "\">

    <div class=\"wcml-section\">
        <div class=\"wcml-section-header\">
            <h3>
                ";
        // line 6
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "heading", array()), "html", null, true);
        echo "
                <i class=\"otgs-ico-help wcml-tip\" data-tip=\"";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "tip", array()), "html", null, true);
        echo "\"></i>
            </h3>
        </div>
        <div class=\"wcml-section-content\">

            <div id=\"wcml-translation-interface-dialog-confirm\" title=\"";
        // line 12
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "heading", array()), "html", null, true);
        echo "\" class=\"hidden\">
                <p>";
        // line 13
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "pb_warning", array()), "html", null, true);
        echo "</p>
                <input type=\"hidden\" class=\"ok-button\" value=\"";
        // line 14
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "pb_warning_ok_button", array()), "html", null, true);
        echo "\" />
                <input type=\"hidden\" class=\"cancel-button\" value=\"";
        // line 15
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "pb_warning_cancel_button", array()), "html", null, true);
        echo "\"/>
            </div>

            <ul>
                <li>
                    <input type=\"radio\" name=\"trnsl_interface\" value=\"";
        // line 20
        echo twig_escape_filter($this->env, ($context["wpml_translation"] ?? null), "html", null, true);
        echo "\"
                            ";
        // line 21
        if (($this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "controls_value", array()) == ($context["wpml_translation"] ?? null))) {
            echo " checked=\"checked\"";
        }
        echo " id=\"wcml_trsl_interface_wcml\" />
                    <label for=\"wcml_trsl_interface_wcml\">";
        // line 22
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "wcml", array()), "label", array()), "html", null, true);
        echo "</label>
                </li>
                <li>
                    <input type=\"radio\" name=\"trnsl_interface\" value=\"";
        // line 25
        echo twig_escape_filter($this->env, ($context["native_translation"] ?? null), "html", null, true);
        echo "\"
                            ";
        // line 26
        if (($this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "controls_value", array()) == ($context["native_translation"] ?? null))) {
            echo " checked=\"checked\"";
        }
        echo " id=\"wcml_trsl_interface_native\" />
                    <label for=\"wcml_trsl_interface_native\">";
        // line 27
        echo $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "translation_interface", array()), "native", array()), "label", array());
        echo "</label>
                </li>
            </ul>

        </div> <!-- .wcml-section-content -->

    </div> <!-- .wcml-section -->

    <div class=\"wcml-section\">

        <div class=\"wcml-section-header\">
            <h3>
                ";
        // line 39
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "synchronization", array()), "heading", array()), "html", null, true);
        echo "
                <i class=\"otgs-ico-help wcml-tip\" data-tip=\"";
        // line 40
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "synchronization", array()), "tip", array()), "html", null, true);
        echo "\"></i>
            </h3>
        </div>

        <div class=\"wcml-section-content\">

            <ul>
                <li>
                    <input type=\"checkbox\" name=\"products_sync_date\" value=\"1\"
                            ";
        // line 49
        if (($this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "synchronization", array()), "sync_date", array()), "value", array()) == 1)) {
            echo " checked=\"checked\"";
        }
        echo " id=\"wcml_products_sync_date\" />
                    <label for=\"wcml_products_sync_date\">";
        // line 50
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "synchronization", array()), "sync_date", array()), "label", array()), "html", null, true);
        echo "</label>
                </li>
                <li>
                    <input type=\"checkbox\" name=\"products_sync_order\" value=\"1\"
                            ";
        // line 54
        if (($this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "synchronization", array()), "sync_order", array()), "value", array()) == 1)) {
            echo " checked=\"checked\"";
        }
        echo " id=\"wcml_products_sync_order\" />
                    <label for=\"wcml_products_sync_order\">";
        // line 55
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "synchronization", array()), "sync_order", array()), "label", array()), "html", null, true);
        echo "</label>
                </li>
            </ul>

        </div>

    </div>


    <div class=\"wcml-section\">

        <div class=\"wcml-section-header\">
            <h3>
                ";
        // line 68
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "file_sync", array()), "heading", array()), "html", null, true);
        echo "
                <i class=\"otgs-ico-help wcml-tip\" data-tip=\"";
        // line 69
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "file_sync", array()), "tip", array()), "html", null, true);
        echo "\"></i>
            </h3>
        </div>

        <div class=\"wcml-section-content\">

            <ul>
                <li>
                    <input type=\"radio\" name=\"wcml_file_path_sync\" value=\"1\"
                            ";
        // line 78
        if (($this->getAttribute($this->getAttribute(($context["form"] ?? null), "file_sync", array()), "value", array()) == 1)) {
            echo " checked=\"checked\"";
        }
        echo " id=\"wcml_file_path_sync_auto\" />
                    <label for=\"wcml_file_path_sync_auto\">";
        // line 79
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "file_sync", array()), "label_same", array()), "html", null, true);
        echo "</label>
                </li>
                <li>
                    <input type=\"radio\" name=\"wcml_file_path_sync\" value=\"0\"
                            ";
        // line 83
        if (($this->getAttribute($this->getAttribute(($context["form"] ?? null), "file_sync", array()), "value", array()) == 0)) {
            echo " checked=\"checked\"";
        }
        echo " id=\"wcml_file_path_sync_self\" />
                    <label for=\"wcml_file_path_sync_self\">";
        // line 84
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "file_sync", array()), "label_diff", array()), "html", null, true);
        echo "</label>
                </li>
            </ul>


        </div> <!-- .wcml-section-content -->

    </div> <!-- .wcml-section -->


    <div class=\"wcml-section cart-sync-section\">
        <div class=\"wcml-section-header\">
            <h3>
                ";
        // line 97
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "heading", array()), "html", null, true);
        echo "
                <i class=\"otgs-ico-help wcml-tip\" data-tip=\"";
        // line 98
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "tip", array()), "html", null, true);
        echo "\"></i>
            </h3>
        </div>
        <div class=\"wcml-section-content\">

            ";
        // line 103
        if ( !$this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "wpml_cookie_enabled", array())) {
            // line 104
            echo "                <i class=\"otgs-ico-warning\"></i>
                <strong>";
            // line 105
            echo $this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "cookie_not_enabled_message", array());
            echo "</strong>
            ";
        }
        // line 107
        echo "
            <div class=\"wcml-section-content-inner\">
                <h4>
                    ";
        // line 110
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "lang_switch", array()), "heading", array()), "html", null, true);
        echo "
                </h4>
                <ul>
                    <li>
                        <input type=\"radio\" name=\"cart_sync_lang\" value=\"";
        // line 114
        echo twig_escape_filter($this->env, ($context["wcml_cart_sync"] ?? null), "html", null, true);
        echo "\"
                                ";
        // line 115
        if (($this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "lang_switch", array()), "value", array()) == ($context["wcml_cart_sync"] ?? null))) {
            echo " checked=\"checked\"";
        }
        // line 116
        echo "                                ";
        if ( !$this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "wpml_cookie_enabled", array())) {
            echo " disabled=\"disabled\"";
        }
        // line 117
        echo "                               id=\"wcml_cart_sync_lang_sync\" />
                        <label for=\"wcml_cart_sync_lang_sync\">";
        // line 118
        echo $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "lang_switch", array()), "sync_label", array());
        echo "</label>
                    </li>
                    <li>
                        <input type=\"radio\" name=\"cart_sync_lang\" value=\"";
        // line 121
        echo twig_escape_filter($this->env, ($context["wcml_cart_clear"] ?? null), "html", null, true);
        echo "\"
                                ";
        // line 122
        if (($this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "lang_switch", array()), "value", array()) == ($context["wcml_cart_clear"] ?? null))) {
            echo " checked=\"checked\"";
        }
        // line 123
        echo "                                ";
        if ( !$this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "wpml_cookie_enabled", array())) {
            echo " disabled=\"disabled\"";
        }
        // line 124
        echo "                               id=\"wcml_cart_sync_lang_clear\" />
                        <label for=\"wcml_cart_sync_lang_clear\">";
        // line 125
        echo $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "lang_switch", array()), "clear_label", array());
        echo "</label>
                    </li>
                </ul>
            </div>
            <div class=\"wcml-section-content-inner\">
                <h4>
                    ";
        // line 131
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "currency_switch", array()), "heading", array()), "html", null, true);
        echo "
                </h4>
                <ul>
                    <li>
                        <input type=\"radio\" name=\"cart_sync_currencies\" value=\"";
        // line 135
        echo twig_escape_filter($this->env, ($context["wcml_cart_sync"] ?? null), "html", null, true);
        echo "\"
                                ";
        // line 136
        if (($this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "currency_switch", array()), "value", array()) == ($context["wcml_cart_sync"] ?? null))) {
            echo " checked=\"checked\"";
        }
        // line 137
        echo "                                ";
        if ( !$this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "wpml_cookie_enabled", array())) {
            echo " disabled=\"disabled\"";
        }
        // line 138
        echo "                               id=\"wcml_cart_sync_curr_sync\" />
                        <label for=\"wcml_cart_sync_curr_sync\">";
        // line 139
        echo $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "currency_switch", array()), "sync_label", array());
        echo "</label>
                    </li>
                    <li>
                        <input type=\"radio\" name=\"cart_sync_currencies\" value=\"";
        // line 142
        echo twig_escape_filter($this->env, ($context["wcml_cart_clear"] ?? null), "html", null, true);
        echo "\"
                                ";
        // line 143
        if (($this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "currency_switch", array()), "value", array()) == ($context["wcml_cart_clear"] ?? null))) {
            echo " checked=\"checked\"";
        }
        // line 144
        echo "                                ";
        if ( !$this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "wpml_cookie_enabled", array())) {
            echo " disabled=\"disabled\"";
        }
        // line 145
        echo "                               id=\"wcml_cart_sync_curr_clear\" />
                        <label for=\"wcml_cart_sync_curr_clear\">";
        // line 146
        echo $this->getAttribute($this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "currency_switch", array()), "clear_label", array());
        echo "</label>
                    </li>
                </ul>
                <p>
                    ";
        // line 150
        echo $this->getAttribute($this->getAttribute(($context["form"] ?? null), "cart_sync", array()), "doc_link", array());
        echo "
                </p>
            </div>
        </div> <!-- .wcml-section-content -->

    </div> <!-- .wcml-section -->

    ";
        // line 157
        echo $this->getAttribute(($context["form"] ?? null), "nonce", array());
        echo "
    <p class=\"wpml-margin-top-sm\">
        <input type='submit' name=\"wcml_save_settings\" value='";
        // line 159
        echo twig_escape_filter($this->env, $this->getAttribute(($context["form"] ?? null), "save_label", array()), "html", null, true);
        echo "' class='button-primary'/>
    </p>
</form>
<a class=\"alignright\" href=\"";
        // line 162
        echo twig_escape_filter($this->env, $this->getAttribute(($context["troubleshooting"] ?? null), "url", array()), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["troubleshooting"] ?? null), "label", array()), "html", null, true);
        echo "</a>";
    }

    public function getTemplateName()
    {
        return "settings-ui.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  357 => 162,  351 => 159,  346 => 157,  336 => 150,  329 => 146,  326 => 145,  321 => 144,  317 => 143,  313 => 142,  307 => 139,  304 => 138,  299 => 137,  295 => 136,  291 => 135,  284 => 131,  275 => 125,  272 => 124,  267 => 123,  263 => 122,  259 => 121,  253 => 118,  250 => 117,  245 => 116,  241 => 115,  237 => 114,  230 => 110,  225 => 107,  220 => 105,  217 => 104,  215 => 103,  207 => 98,  203 => 97,  187 => 84,  181 => 83,  174 => 79,  168 => 78,  156 => 69,  152 => 68,  136 => 55,  130 => 54,  123 => 50,  117 => 49,  105 => 40,  101 => 39,  86 => 27,  80 => 26,  76 => 25,  70 => 22,  64 => 21,  60 => 20,  52 => 15,  48 => 14,  44 => 13,  40 => 12,  32 => 7,  28 => 6,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "settings-ui.twig", "/home/welln479/public_html/wp-content/plugins/woocommerce-multilingual/templates/settings-ui.twig");
    }
}
