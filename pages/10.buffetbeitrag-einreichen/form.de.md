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
            destination: 'page@:/gemeindetag'
            filesize: 5
            accept:
                - 'image/*'
            validate:
                required: false
        g-recaptcha-response:
          label: Code
          type: captcha
          recaptcha_site_key: 6LcLBP0UAAAAABLvELpXzeTPmI0j5_2Om7zEeDOI
          recaptcha_not_validated: 'Code ist ungültig'
          validate:
            required: true
          process:
            ignore: true
    buttons:
        submit:
            type: submit
            value: Absenden
    process:
        reset: 
            true
        #captcha: 
         #   recaptcha_secret: 6LcLBP0UAAAAAOIYAOqXuzZxyS-fCLfLLtX48dyc
        save:
            fileprefix: buffet-
            dateformat: Ymd-His-u
            extension: txt
            body: '{% include ''forms/data.txt.twig'' %}'
        #redirect: '/gemeindetag'
        email:
            subject: '[Buffetbeitrag] {{ form.value.name|e }}'
            body: '{% include ''forms/data.html.twig'' %}'
        message: 'Danke für deinen Beitrag!'
---

# Büffetbeitrag einreichen

---

