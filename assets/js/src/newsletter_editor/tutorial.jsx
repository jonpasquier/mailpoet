import MailPoet from 'mailpoet';
import moment from 'moment';

const displayTutorial = () => {
  const key = `user_seen_editor_tutorial${window.config.currentUserId}`;
  if (window.config.dragDemoUrlSettings) {
    return;
  }
  if (moment(window.config.installedAt).isBefore(moment().subtract(7, 'days'))) {
    return;
  }
  MailPoet.Modal.popup({
    template: `<div class="mailpoet_drag_and_drop_tutorial"><h2>${MailPoet.I18n.t('tutorialVideoTitle')}</h2><video style="height:640px;" src="${window.config.dragDemoUrl}" controls autoplay></video></div>`,
    onCancel: () => {
      MailPoet.Ajax.post({
        api_version: window.mailpoet_api_version,
        endpoint: 'settings',
        action: 'set',
        data: { [key]: 1 },
      });
    },
  });
};

export default displayTutorial;
