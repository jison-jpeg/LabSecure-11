import './bootstrap';

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();
window.Echo.channel('attendance-channel')
    .listen('.attendance.recorded', (data) => {
        console.log('Attendance Recorded:', data);
        // Handle the real-time update here
    });
