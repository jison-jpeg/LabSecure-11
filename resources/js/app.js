import "./bootstrap";

// import Alpine from 'alpinejs';

// window.Alpine = Alpine;

// Alpine.start();

import { Notyf } from "notyf";
import "notyf/notyf.min.css";

const notyf = new Notyf({
    position: {
        x: "right",
        y: "top",
    },
    duration: 5000,
    ripple: true,
    dismissible: true,
});

// Listen for attendance recorded public channel
window.Echo.channel("attendance-channel").listen(
    ".attendance.recorded",
    (data) => {
        // console.log("Attendance Recorded:", data);
        // notyf.success(
        //     `Attendance recorded successfully for ${data.user.first_name} ${data.user.last_name} RFID: ${data.user.rfid_number}`
        // );

        // Emit a browser event
        window.dispatchEvent(
            new CustomEvent("refresh-attendance-table", {
                detail: {
                    attendance: data,
                },
            })
        );
    }
);

// Listen for laboratory status updated public channel
window.Echo.channel("laboratory-channel").listen(
    ".laboratory.status.updated",
    (data) => {
        // console.log("Laboratory Status Updated:", data);
        // notyf.success(
        //     `Laboratory status updated to ${data.status} for ${data.name}`
        // );
        // Update the lab status on the page
        const labElement = document.querySelector(`#laboratory-${data.id}`);
        if (labElement) {
            labElement.querySelector(".status").innerText = data.status;
        }

        // Emit a browser event
        window.dispatchEvent(
            new CustomEvent("refresh-laboratory-table", {
                detail: {
                    laboratory: data,
                },
            })
        );
    }
);

// Listen for NFC card detection on the public channel
window.Echo.channel("nfc-channel").listen(".nfc.card.detected", (data) => {
    console.log("NFC Card Detected:", data.card_id);

    // Automatically update the RFID input field
    const rfidInputField = document.querySelector("#rfid_number"); // Updated ID
    if (rfidInputField) {
        rfidInputField.value = data.card_id;

        // Dispatch the 'input' and 'change' events
        rfidInputField.dispatchEvent(new Event("input"));
        rfidInputField.dispatchEvent(new Event("change"));
    }

    // Optionally alert the user
    // alert(`NFC Card Detected! Card ID: ${data.card_id}`);

    // Emit a custom browser event for further processing
    window.dispatchEvent(
        new CustomEvent("nfc-card-detected", {
            detail: {
                cardId: data.card_id,
            },
        })
    );
});
