// Skeleton Elements Utility Functions
window.SkeletonUI = {
    // Show skeleton loading state
    showSkeleton: function(container, type = 'text', count = 1) {
        const parent = document.querySelector(container);
        if (!parent) return;

        // Store original content
        parent.setAttribute('data-original', parent.innerHTML);
        parent.innerHTML = '';

        for (let i = 0; i < count; i++) {
            switch(type) {
                case 'text':
                    parent.appendChild(this.createSkeletonText());
                    break;
                case 'card':
                    parent.appendChild(this.createSkeletonCard());
                    break;
                case 'table-row':
                    parent.appendChild(this.createSkeletonTableRow());
                    break;
                case 'list-item':
                    parent.appendChild(this.createSkeletonListItem());
                    break;
                case 'image':
                    parent.appendChild(this.createSkeletonImage());
                    break;
            }
        }
    },

    // Hide skeleton loading state and restore content
    hideSkeleton: function(container) {
        const parent = document.querySelector(container);
        if (!parent) return;

        const originalContent = parent.getAttribute('data-original');
        if (originalContent) {
            parent.innerHTML = originalContent;
            parent.removeAttribute('data-original');
        }
    },

    // Create skeleton text element
    createSkeletonText: function() {
        const div = document.createElement('div');
        div.className = 'skeleton-text';
        return div;
    },

    // Create skeleton card with multiple text lines
    createSkeletonCard: function() {
        const card = document.createElement('div');
        card.className = 'skeleton-card';
        
        // Add header
        const header = this.createSkeletonText();
        header.className += ' large';
        card.appendChild(header);

        // Add content lines
        for (let i = 0; i < 3; i++) {
            card.appendChild(this.createSkeletonText());
        }

        return card;
    },

    // Create skeleton table row
    createSkeletonTableRow: function() {
        const row = document.createElement('div');
        row.className = 'skeleton-table-row';
        
        for (let i = 0; i < 4; i++) {
            const cell = document.createElement('div');
            cell.className = 'skeleton-table-cell';
            row.appendChild(cell);
        }

        return row;
    },

    // Create skeleton list item
    createSkeletonListItem: function() {
        const item = document.createElement('div');
        item.className = 'skeleton-list-item';
        return item;
    },

    // Create skeleton image
    createSkeletonImage: function() {
        const img = document.createElement('div');
        img.className = 'skeleton-image';
        return img;
    }
};
