---
title: form
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
                required: true
        message:
            label: Nachricht
            placeholder: 'Nachricht hinzufügen'
            type: textarea
            validate:
                required: true
        image:
            label: 'Bild (max. 5 MB)'
            placeholder: 'Bild hochladen'
            type: file
            multiple: false
            destination: '@self'
            filesize: 5
            accept:
                - 'image/*'
            validate:
                required: false
    buttons:
        submit:
            type: submit
            value: Absenden
    process:
        captcha: true
        save:
            fileprefix: contact-
            dateformat: Ymd-His-u
            extension: txt
            body: '{% include ''forms/data.txt.twig'' %}'
        email:
            subject: '[Site Contact Form] {{ form.value.name|e }}'
            body: '{% include ''forms/data.html.twig'' %}'
        message: 'Thank you for getting in touch!'
        display: thankyou
---

# Büffetbeitrag einreichen

---

