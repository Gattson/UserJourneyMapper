jQuery(document).ready(function($) {
	console.log('User Journey Mapper admin script loaded.');
	
     const canvas = document.getElementById('ujmJourneyChart');
    if (typeof ujmJourneyLabels !== 'undefined' && canvas) {
        const ctx = canvas.getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ujmJourneyLabels,
                datasets: [{
                    label: 'User Path',
                    data: ujmJourneyLabels.map((_, i) => i + 1),
                    fill: false,
                    borderColor: '#007cba',
                    tension: 0.3,
                    pointBackgroundColor: '#007cba',
                    pointRadius: 5
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { display: false },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    } else {
        console.warn('Chart not rendered: ujmJourneyLabels or canvas missing.');
    }
});
