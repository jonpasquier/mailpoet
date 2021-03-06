define(
  [
    'backbone',
    'jquery',
    'mailpoet'
  ],
  function ( // eslint-disable-line func-names
    Backbone,
    jQuery,
    mp
  ) {
    var MailPoet = mp;
    if (jQuery('#mailpoet_settings').length === 0) {
      return;
    }

    MailPoet.Router = new (Backbone.Router.extend({
      routes: {
        '': 'defaultRoute',
        'mta(/:group)': 'sendingMethodGroup',
        '(:tab)': 'tabs'
      },
      defaultRoute: function () { // eslint-disable-line func-names
        // display basics tab as default
        this.tabs('basics');
      },
      sendingMethodGroup: function (group) { // eslint-disable-line func-names
        // display mta tab
        this.tabs('mta');

        // hide all sending methods' settings
        jQuery(
          '#mailpoet_sending_method_setup, .mailpoet_sending_method'
        ).hide();

        // hide "save settings" button
        jQuery('.mailpoet_settings_submit').hide();

        if (group === null) {
          // show sending methods
          jQuery('.mailpoet_sending_methods, .mailpoet_sending_methods_help').fadeIn();
        } else {
          // toggle SPF (hidden if the sending method is MailPoet)
          jQuery('#mailpoet_mta_spf')[
            (group === 'mailpoet')
              ? 'hide'
              : 'show'
          ]();

          // hide sending methods
          jQuery('.mailpoet_sending_methods, .mailpoet_sending_methods_help').hide();

          // display selected sending method's settings
          jQuery('.mailpoet_sending_method[data-group="' + group + '"]').show();
          jQuery('#mailpoet_sending_method_setup').fadeIn();
        }
      },
      tabs: function (tab) { // eslint-disable-line func-names
        // reset all active tabs
        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');

        // hide panels & sections
        jQuery('.mailpoet_tab_panel, .mailpoet_section').hide();

        // set active tab
        jQuery('a.nav-tab[href="#' + tab + '"]').addClass('nav-tab-active').blur();

        // show selected panel
        if (jQuery('.mailpoet_tab_panel[data-tab="' + tab + '"]').length > 0) {
          jQuery('.mailpoet_tab_panel[data-tab="' + tab + '"]').show();
        }

        // show "save settings" button
        jQuery('.mailpoet_settings_submit').show();

        MailPoet.trackEvent(
          'User has clicked a tab in Settings',
          {
            'MailPoet Free version': window.mailpoet_version,
            'Tab ID': tab
          }
        );
      }
    }))();

    jQuery(document).ready(function () { // eslint-disable-line func-names
      if (!Backbone.History.started) Backbone.history.start();
    });
  }
);
