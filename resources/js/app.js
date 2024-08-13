import './bootstrap';

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();

import { Notyf } from 'notyf';
import 'notyf/notyf.min.css'; 

const notyf = new Notyf({
    position: {
        x: 'right',
        y: 'top',
    },
    duration: 5000,
    ripple: true,
    dismissible: true
});

window.Echo.channel('attendance-channel')
    .listen('.attendance.recorded', (data) => {
        console.log('Attendance Recorded:', data);
        notyf.success(`Attendance recorded successfully for ${data.user.first_name} ${data.user.last_name} RFID: ${data.user.rfid_number}`);
    });
