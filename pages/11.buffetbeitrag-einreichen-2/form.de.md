---
title: 'form 2'
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
            label: Nachricht
            size: long
            placeholder: 'Nachricht hinzufügen'
            type: textarea
            validate:
                required: true
        image:
            label: 'Bild (max. 5 MB)'
            placeholder: 'Bild hochladen'
            type: file
            multiple: false
            destination: 'page@:/gemeindetag'
            filesize: 5
            accept:
                - 'image/*'
            validate:
                required: false
    buttons:
        submit:
            type: submit
            value: Absenden
            classes: event-button
    process:
        save:
            fileprefix: impression-
            dateformat: Ymd-His-u
            extension: txt
            body: '{% include ''forms/data.txt.twig'' %}'
        email:
            subject: '[Impression] {{ form.value.name|e }}'
            body: '{% include ''forms/data.html.twig'' %}'
        message: 'Danke für deinen Beitrag!'
---

# Impression vom Gemeindetag teilen

---
[Zurück zum Gemeindetag](/gemeindetag)
