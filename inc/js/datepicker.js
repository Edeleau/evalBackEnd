
import Datepicker from '../../node_modules/vanillajs-datepicker/js/Datepicker.js';
import DateRangePicker from '../../node_modules/vanillajs-datepicker/js/DateRangePicker.js';
import fr from '../../node_modules/vanillajs-datepicker/js/i18n/locales/fr.js';
Object.assign(Datepicker.locales, fr);

if (document.getElementById('datepicker')) {


    const elem = document.getElementById('datepicker');
    const rangepicker = new DateRangePicker(elem, {
        format: 'dd-mm-yyyy',
        language: 'fr',
        buttonClass: 'btn',
        minDate: Date.now()
    });
}
if (document.getElementById('timepicker')) {

    new Picker(document.querySelector('.js-time-pickerStart'), {
        format: 'HH:mm',
        headers: true,
        controls: true,
        text: {
            title: 'Choisissez une heure d\'arrivée',
            cancel: 'Annuler',
            confirm: 'OK',
            year: 'Année',
            month: 'Mois',
            day: 'Jour',
            hour: 'Heure',
            minute: 'Minute',
            second: 'Seconde',
        },
        increment: {
            minute: 5,
        },
    });
    new Picker(document.querySelector('.js-time-pickerEnd'), {
        format: 'HH:mm',
        headers: true,
        controls: true,
        text: {
            title: 'Choisissez une heure de départ',
            cancel: 'Annuler',
            confirm: 'OK',
            year: 'Année',
            month: 'Mois',
            day: 'Jour',
            hour: 'Heure',
            minute: 'Minute',
            second: 'Seconde',
        },
        increment: {
            minute: 5,
        },

    });
}
