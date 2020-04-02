---
title: Veranstaltungen
content:
    items:
        '@taxonomy':
            type: event
    order:
        by: date
        dir: asc
    limit: '10'
    pagination: true
---

{% embed 'partials/base.html.twig' %}

{# get events by taxonomy #}
{% set events =
    page.collection({
      'items': {
        '@taxonomy': {
          'type': 'event',
        }
      },
      'dateRange': {
        'start': datetools.today|date('m/d/Y'),
        'end': "now + 1 year"|date('m/d/Y')
      },
      'order': {
        'by': 'date',
        'dir': 'asc'
      },
      'limit': 1000,
      'pagination': false
    })
%}

{% block content %}

  <div class="events-container">

    <header>
      <p class="center">vom {{datetools.today|date('d.m.Y')}} bis {{"now + 1 year"|date("d.m.Y")}}</p>
      <p class="warning"><strong>Diese Seite befindet sich im Aufbau. Deshalb kann es bei den Terminen noch zu Abweichungen kommen.</strong></p>
    </header>

    <section class="featured-events">
    Filtern nach Monat
    {# include 'partials/taxonomylistManual.html.twig' with {base_url: page.url, taxonomy: 'tag' } #}
    </section>

    <section class="events-listing">
      {% for item in events %}

        <article class="event-item">
          <section class="event-content">
            <h3>{{ item.title }}</h3>
            <div class="event-meta">
              <ul>
                <li class="when"><i class="fa fa-calendar"></i>{{'DAYS_OF_THE_WEEK'|ta(item.header.event.start|date('N')-1)}}, {{item.header.event.start|date('d.')}} {{config.plugins.events.date_format.translate ? 'MONTHS_OF_THE_YEAR'|ta(item.header.event.start|date('n') - 1) : item.header.event.start|date('M') }} {{item.header.event.start|date('Y')}}</li>
                <li class="when"><i class="fa fa-clock-o"></i>{{item.header.event.start|date('H:i')}} Uhr</li>
              </ul>
              <a href="{{ item.url }}" class="event-button">{{ 'PLUGIN_EVENTS.EVENTS.BUTTON'|t }}</a>
            </div>
          </section>
        </article>
      {% endfor %}
    </section>
	</div>

  {# Render the pagination list #}
  {% if config.plugins.pagination.enabled and events.params.pagination %}
      {% include 'partials/pagination.html.twig' with {'base_url':page.url, 'pagination':collection.params.pagination} %}
  {% endif %}

{% endblock %}

{% endembed %}
