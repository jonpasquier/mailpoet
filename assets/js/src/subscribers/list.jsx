define(
  [
    'react',
    'react-router',
    'listing/listing.jsx',
    'classnames',
    'mailpoet',
    'jquery',
    'select2'
  ],
  function(
    React,
    Router,
    Listing,
    classNames,
    MailPoet,
    jQuery
  ) {
    var Link = Router.Link;

    var columns = [
      {
        name: 'email',
        label: 'Email',
        sortable: true
      },
      {
        name: 'first_name',
        label: 'Firstname',
        sortable: true
      },
      {
        name: 'last_name',
        label: 'Lastname',
        sortable: true
      },
      {
        name: 'status',
        label: 'Status',
        sortable: true
      },
      {
        name: 'lists',
        label: 'Lists',
        sortable: false
      },

      {
        name: 'created_at',
        label: 'Subscribed on',
        sortable: true
      },
      {
        name: 'updated_at',
        label: 'Last modified on',
        sortable: true
      },
    ];

    var ItemSelection = React.createClass({
      getInitialState: function() {
        return {
          loading: false,
          items: [],
          selected: false,
          multiple: false
        }
      },
      componentDidMount: function() {
        // this.loadItems();
        this.loadCachedItems();
      },
      loadCachedItems: function() {
        if(typeof(window['mailpoet_'+this.props.endpoint]) !== 'undefined') {
          var items = window['mailpoet_'+this.props.endpoint];
          this.setState({
            items: items
          });
        }
      },
      loadItems: function() {
        this.setState({ loading: true });

        MailPoet.Ajax.post({
          endpoint: this.props.endpoint,
          action: 'listing',
          data: {
            'offset': 0,
            'limit': 100,
            'search': '',
            'sort_by': 'name',
            'sort_order': 'asc'
          }
        })
        .done(function(response) {
          if(this.isMounted()) {
            if(response === false) {
              this.setState({
                loading: false,
                items: []
              });
            } else {
              this.setState({
                loading: false,
                items: response.items
              });
            }
          }
        }.bind(this));
      },
      handleChange: function() {
        var new_value = this.refs.selection.getDOMNode().value;

        if(this.state.multiple === false) {
          if(new_value.trim().length === 0) {
            new_value = false;
          }

          this.setState({
            selected: new_value
          });
        } else {
          var selected_values = this.state.selected || [];

          if(selected_values.indexOf(new_value) !== -1) {
            // value already present so remove it
            selected_values.splice(selected_values.indexOf(new_value), 1);
          } else {
            selected_values.push(new_value);
          }

          this.setState({
            selected: selected_values
          });
        }
      },
      getSelected: function() {
        return this.state.selected;
      },
      render: function() {
        var options = this.state.items.map(function(item, index) {
          return (
            <option
              key={ 'action-' + index }
              value={ item.id }>
              { item.name }
            </option>
          );
        });

        return (
          <select
            ref="selection"
            id={ this.props.id }
            value={ this.state.selected }
            onChange={ this.handleChange }
            multiple={ this.state.multiple }>
            <option value="">Select a list</option>
            { options }
          </select>
        );
      }
    });

    var bulk_actions = [
      {
        name: 'moveToList',
        label: 'Move to list...',
        onSelect: function() {
          return (
            <ItemSelection
              endpoint="segments"
              id="move_to_segment" />
          );
        },
        getData: function() {
          return {
            segment_id: ~~(jQuery('#move_to_segment').val())
          }
        }
      },
      {
        name: 'addToList',
        label: 'Add to list...',
        onSelect: function() {
          return (
            <ItemSelection
              endpoint="segments"
              id="add_to_segment" />
          );
        },
        getData: function() {
          return {
            segment_id: ~~(jQuery('#add_to_segment').val())
          }
        }
      },
      {
        name: 'removeFromList',
        label: 'Remove from list...',
        onSelect: function() {
          return (
            <ItemSelection
              endpoint="segments"
              id="remove_from_segment" />
          );
        },
        getData: function() {
          return {
            segment_id: ~~(jQuery('#remove_from_segment').val())
          }
        }
      },
      {
        name: 'trash',
        label: 'Trash'
      }
    ];

    var SubscriberList = React.createClass({
      renderItem: function(subscriber, actions) {
        var row_classes = classNames(
          'manage-column',
          'column-primary',
          'has-row-actions'
        );

        var status = '';

        switch(subscriber.status) {
          case 'subscribed':
            status = 'Subscribed';
          break;

          case 'unconfirmed':
            status = 'Unconfirmed';
          break;

          case 'unsubscribed':
            status = 'Unsubscribed';
          break;
        }

        var segments = mailpoet_segments.filter(function(segment) {
          return (jQuery.inArray(segment.id, subscriber.segments) !== -1);
        }).map(function(segment) {
          return segment.name;
        }).join(', ');

        return (
          <div>
            <td className={ row_classes }>
              <strong>
                <Link to="edit" params={{ id: subscriber.id }}>
                  { subscriber.email }
                </Link>
              </strong>
              { actions }
            </td>
            <td className="column" data-colname="First name">
              { subscriber.first_name }
            </td>
            <td className="column" data-colname="Last name">
              { subscriber.last_name }
            </td>
            <td className="column" data-colname="Status">
              { status }
            </td>
            <td className="column" data-colname="Status">
              { segments }
            </td>
            <td className="column-date" data-colname="Subscribed on">
              <abbr>{ subscriber.created_at }</abbr>
            </td>
            <td className="column-date" data-colname="Last modified on">
              <abbr>{ subscriber.updated_at }</abbr>
            </td>
          </div>
        );
      },
      render: function() {
        return (
          <Listing
            endpoint="subscribers"
            onRenderItem={ this.renderItem }
            columns={ columns }
            bulk_actions={ bulk_actions } />
        );
      }
    });

    return SubscriberList;
  }
);