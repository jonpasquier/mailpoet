import React from 'react';
import ReactDOM from 'react-dom';
import { HashRouter, Switch, Route } from 'react-router-dom';

import SubscriberList from 'subscribers/list.jsx';
import SubscriberForm from 'subscribers/form.jsx';

const container = document.getElementById('subscribers_container');

if (container) {
  ReactDOM.render((
    <HashRouter>
      <Switch>
        <Route path="/new" component={SubscriberForm} />
        <Route path="/edit/:id" component={SubscriberForm} />
        <Route path="*" component={SubscriberList} />
      </Switch>
    </HashRouter>
  ), container);
}
