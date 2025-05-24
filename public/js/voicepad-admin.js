// VoicePad Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeAudioPlayers();
    initializePlayButtons();
    initializeDragDropReordering();
});

/**
 * Initialize audio players with lazy loading
 */
function initializeAudioPlayers() {
    const audioElements = document.querySelectorAll('audio[preload="metadata"]');
    
    audioElements.forEach(audio => {
        // Lazy load audio when it comes into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const audioEl = entry.target;
                    audioEl.load();
                    observer.unobserve(audioEl);
                }
            });
        });
        
        observer.observe(audio);
        
        // Add event listeners for audio events
        audio.addEventListener('loadstart', function() {
            this.parentElement.classList.add('sound-loading');
        });
        
        audio.addEventListener('canplay', function() {
            this.parentElement.classList.remove('sound-loading');
        });
        
        audio.addEventListener('error', function() {
            this.parentElement.classList.add('sound-error');
            console.error('Failed to load audio:', this.src);
        });
    });
}

/**
 * Initialize play buttons in table rows
 */
function initializePlayButtons() {
    let currentlyPlaying = null;
    
    document.addEventListener('click', function(e) {
        if (e.target.closest('.play-sound-btn')) {
            e.preventDefault();
            
            const button = e.target.closest('.play-sound-btn');
            const soundUrl = button.dataset.soundUrl;
            
            if (!soundUrl) return;
            
            // Stop currently playing audio
            if (currentlyPlaying && !currentlyPlaying.paused) {
                currentlyPlaying.pause();
                currentlyPlaying.currentTime = 0;
                updatePlayButtonState(currentlyPlaying.dataset.button, false);
            }
            
            // Create or get audio element
            let audio = document.querySelector(`audio[data-url="${soundUrl}"]`);
            if (!audio) {
                audio = document.createElement('audio');
                audio.src = soundUrl;
                audio.preload = 'metadata';
                audio.dataset.url = soundUrl;
                audio.dataset.button = button.dataset.soundUrl;
                document.body.appendChild(audio);
                
                audio.addEventListener('ended', function() {
                    updatePlayButtonState(button, false);
                    currentlyPlaying = null;
                });
            }
            
            // Play audio
            audio.play().then(() => {
                currentlyPlaying = audio;
                updatePlayButtonState(button, true);
            }).catch(error => {
                console.error('Failed to play audio:', error);
            });
        }
    });
}

/**
 * Update play button state
 */
function updatePlayButtonState(button, isPlaying) {
    const icon = button.querySelector('svg');
    const label = button.querySelector('.fi-btn-label');
    
    if (isPlaying) {
        button.classList.add('fi-color-danger');
        button.classList.remove('fi-color-success');
        if (label) label.textContent = 'Stop';
    } else {
        button.classList.add('fi-color-success');
        button.classList.remove('fi-color-danger');
        if (label) label.textContent = 'Play';
    }
}

/**
 * Initialize drag and drop reordering
 */
function initializeDragDropReordering() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && 
                mutation.attributeName === 'class' && 
                mutation.target.classList.contains('sortable-ghost')) {
                
                mutation.target.style.transform = 'rotate(5deg)';
                mutation.target.style.boxShadow = '0 5px 15px rgba(0,0,0,0.3)';
            }
        });
    });
    
    const tableRows = document.querySelectorAll('.fi-ta-row');
    tableRows.forEach(row => {
        observer.observe(row, { attributes: true });
    });
}

/**
 * File upload enhancements
 */
function initializeFileUploadEnhancements() {
    document.addEventListener('change', function(e) {
        if (e.target.matches('input[type="file"][accept*="audio"]')) {
            const file = e.target.files[0];
            if (file) {
                displayFileInfo(file, e.target);
                
                extractAudioDuration(file).then(duration => {
                    if (duration) {
                        const durationField = document.querySelector('input[name="detected_duration"]');
                        if (durationField) {
                            durationField.value = duration.toFixed(2);
                        }
                    }
                });
            }
        }
    });
}

/**
 * Display file information after selection
 */
function displayFileInfo(file, input) {
    const container = input.closest('.fi-fo-file-upload');
    let infoDiv = container.querySelector('.file-info');
    
    if (!infoDiv) {
        infoDiv = document.createElement('div');
        infoDiv.className = 'file-info mt-2 p-2 bg-gray-50 rounded text-sm';
        container.appendChild(infoDiv);
    }
    
    const size = formatFileSize(file.size);
    const type = file.type || 'Unknown';
    
    infoDiv.innerHTML = `
        <div><strong>File:</strong> ${file.name}</div>
        <div><strong>Size:</strong> ${size}</div>
        <div><strong>Type:</strong> ${type}</div>
    `;
}

/**
 * Extract audio duration from file
 */
function extractAudioDuration(file) {
    return new Promise((resolve) => {
        const audio = document.createElement('audio');
        audio.preload = 'metadata';
        
        audio.onloadedmetadata = function() {
            resolve(audio.duration);
            URL.revokeObjectURL(audio.src);
        };
        
        audio.onerror = function() {
            resolve(null);
            URL.revokeObjectURL(audio.src);
        };
        
        audio.src = URL.createObjectURL(file);
    });
}

/**
 * Format file size to human readable format
 */
function formatFileSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unit = 0;
    
    while (size >= 1024 && unit < units.length - 1) {
        size /= 1024;
        unit++;
    }
    
    return `${size.toFixed(2)} ${units[unit]}`;
}

// Initialize file upload enhancements
initializeFileUploadEnhancements();
