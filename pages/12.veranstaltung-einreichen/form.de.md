---
title: 'Veranstaltung einreichen'
visible: false
form:
    name: contact
    fields:
        name:
            label: Name
            placeholder: 'Vor- und Nachname eingeben'
            autocomplete: 'on'
            type: text
            validate:
                required: true
        email:
            label: E-Mail
            placeholder: 'E-Mail-Adresse eingeben'
            type: email
            validate:
                rule: email
                required: true
        message:
            label: Idee
            size: long
            placeholder: 'Bitte Idee kurz beschreiben'
            type: textarea
            validate:
                required: true
        date:
            type: date
            label: Wunschdatum
            toggleable: false
            validate:
                required: false
    buttons:
        submit:
            type: submit
            value: Absenden
            classes: event-button
    process:
        save:
            fileprefix: veranstaltung-
            dateformat: Ymd-His-u
            extension: txt
            body: '{% include ''forms/data.txt.twig'' %}'
        email:
            subject: '[Impression] {{ form.value.name|e }}'
            body: '{% include ''forms/data.html.twig'' %}'
        message: 'Danke für deinen Beitrag!'
        display: thankyou
---

# Veranstaltung einreichen

---

