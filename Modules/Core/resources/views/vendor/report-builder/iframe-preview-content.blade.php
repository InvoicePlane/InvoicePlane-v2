{{-- Override of Mason's iframe-preview-content to display actual brick previews --}}
<style>
#mason-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 0;
}

/*
 * Drop zones sit between the blocks in document order, but a full-width bar
 * between two half-width blocks would push them onto separate rows and wreck
 * the very layout being edited. So a zone takes up no space at all until a
 * drag is under way, and only then expands into a full-width bar in its real
 * position. They used to carry order: 9999 instead, which kept the layout
 * intact but stacked every drop target at the bottom of the canvas, nowhere
 * near the place the brick would actually land.
 */
.mason-drop-zone {
    display: none;
    flex-basis: 100%;
    min-height: 2rem;
    background-color: #FFFACD;
    border: 2px dashed #FFD700;
    border-radius: 4px;
    margin: 6px 0;
    transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    opacity: 0.7;
}

body.mason-dragging .mason-drop-zone:not(.mason-drop-zone--empty) {
    display: block;
}

/* The empty-band prompt is the canvas itself, so it is always on show. */
.mason-drop-zone--empty {
    display: flex;
}

.mason-drop-zone:hover,
.mason-drop-zone.active {
    background-color: #FFE135;
    border-color: #FFA500;
    box-shadow: 0 0 12px rgba(255, 165, 0, 0.6);
    opacity: 1;
}

.mason-block {
    flex-grow: 0;
    flex-shrink: 0;
    flex-basis: auto;
    min-width: 0;
    padding-right: 8px;
    box-sizing: border-box;
    transition: box-shadow 0.2s ease;
}

.mason-block.dragging {
    opacity: 0.7;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.mason-block-content {
    width: 100%;
    font-size: 0;
}
.mason-block-content > * {
    font-size: 16px;
}

.mason-block-controls {
    padding: 4px;
}

.mason-block-btn {
    transition: all 0.2s ease;
}

.mason-block-btn:hover {
    transform: scale(1.1);
    color: #0ea5e9;
}

.mason-block:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
<div id="mason-preview-container">
    @if (empty($blocks))
        <div
            class="mason-drop-zone mason-drop-zone--empty"
            data-drop-index="0"
            style="
                min-height: 4rem;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #9ca3af;
                position: absolute;
                inset: 0;
                margin: 0;
            "
        >
            {{ __('mason::mason.preview.placeholder') }}
        </div>
    @else
        <div
            class="mason-drop-zone"
            data-drop-index="0"
            style="min-height: 2rem"
        ></div>
        @foreach ($blocks as $block)
            <div
                class="mason-block"
                draggable="true"
                data-block-index="{{ $block['index'] }}"
                data-brick-id="{{ $block['id'] }}"
                data-config="{{ json_encode($block['config']) }}"
                data-total-blocks="{{ count($blocks) }}"
            >
                <div class="mason-block-controls">
                    <button
                        class="mason-block-btn"
                        title="Move Up"
                        data-action="move-up"
                        data-block-index="{{ $block['index'] }}"
                        data-total-blocks="{{ count($blocks) }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><title>{{ __('mason::mason.preview.move_up') }}</title><polyline points="18 15 12 9 6 15"></polyline></svg>
                    </button>
                    <button
                        class="mason-block-btn"
                        title="Move Down"
                        data-action="move-down"
                        data-block-index="{{ $block['index'] }}"
                        data-total-blocks="{{ count($blocks) }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><title>{{ __('mason::mason.preview.move_down') }}</title><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <button
                        class="mason-block-btn"
                        title="{{ __('mason::mason.preview.add') }}"
                        data-action="add"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><title>{{ __('mason::mason.preview.add') }}</title><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                    </button>
                    <button
                        class="mason-block-btn"
                        title="Edit"
                        data-action="edit"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><title>{{ __('mason::mason.preview.edit') }}</title><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0 0 10 3H4.75A2.75 2.75 0 0 0 2 5.75v9.5A2.75 2.75 0 0 0 4.75 18h9.5A2.75 2.75 0 0 0 17 15.25V10a.75.75 0 0 0-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5Z" /></svg>
                    </button>
                    <button
                        class="mason-block-btn"
                        title="Delete"
                        data-action="delete"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><title>{{ __('mason::mason.preview.delete') }}</title><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
                <div class="mason-block-content" style="overflow: auto; clear: both;">
                    {{-- Decode the base64-encoded preview HTML from the brick --}}
                    @php
                        $decodedHtml = '';
                        if (isset($block['preview']) && is_string($block['preview'])) {
                            $decodedHtml = base64_decode($block['preview']);
                        } elseif (isset($block['html']) && is_string($block['html'])) {
                            $decodedHtml = $block['html'];
                        }
                    @endphp
                    {!! $decodedHtml !!}
                </div>
            </div>
            <div
                class="mason-drop-zone"
                data-drop-index="{{ $block['index'] + 1 }}"
            ></div>
        @endforeach
    @endif
</div>

<script>
    ;(function () {
        const container = document.getElementById('mason-preview-container')
        // Keep the semicolon: a Blade directive that ends a line swallows the
        // newline after it, welding this onto the statement below.
        const placeholderText = @json(__('mason::mason.preview.placeholder'));
        const INSTANCE_NAME_PREFIX = 'mason-preview-iframe-'

        let selectedBlock = null
        let dblClickToEdit = false
        let isDisabled = false

        /*
         * Every band on the page runs its own Mason component, and they all
         * listen on the same parent window, filtering by instanceId. Waiting
         * for the parent to tell us who we are left a window in which our
         * messages carried instanceId: null — which no component filters out,
         * so a single click could be acted on by all five bands at once. The
         * iframe's own window.name already encodes the state path, so read it
         * from there and only fall back to the parent's word for it.
         */
        let instanceId = window.name && window.name.startsWith(INSTANCE_NAME_PREFIX)
            ? window.name.slice(INSTANCE_NAME_PREFIX.length)
            : null

        function postToParent(message) {
            window.parent.postMessage({ ...message, instanceId }, '*')
        }

        window.addEventListener('message', function (event) {
            const { type, instanceId: msgInstanceId, ...data } = event.data

            if (msgInstanceId && !instanceId) {
                instanceId = msgInstanceId
            }

            if (msgInstanceId && instanceId && msgInstanceId !== instanceId) {
                return
            }

            switch (type) {
                case 'setContent':
                    if (data.dblClickToEdit !== undefined) {
                        dblClickToEdit = data.dblClickToEdit
                    }
                    shouldBeDisabled(data)
                    updateContent(data.blocks)
                    break
                case 'setConfig':
                    if (data.dblClickToEdit !== undefined) {
                        dblClickToEdit = data.dblClickToEdit
                    }
                    shouldBeDisabled(data)
                    break
                case 'insertBlock':
                    insertBlock(data.brick, data.position)
                    break
                case 'updateBlock':
                    updateBlock(data.index, data.brick)
                    break
                case 'deleteBlock':
                    deleteBlock(data.index)
                    break
                case 'moveBlock':
                    moveBlock(data.from, data.to)
                    break
                case 'selectBlock':
                    selectBlock(data.index)
                    break
                case 'updateMoveButtons':
                    updateAllMoveButtons()
                    break
                case 'deselectAllBlocks':
                    deselectAllBlocks()
                    break
                case 'setColorMode':
                    if (data.mode === 'dark') {
                        document.documentElement.classList.add('dark')
                    } else {
                        document.documentElement.classList.remove('dark')
                    }
                    break
            }
        })

        function applyBlockWidths() {
            document.querySelectorAll('.mason-block').forEach(blockEl => {
                const configStr = blockEl.getAttribute('data-config')
                if (configStr) {
                    try {
                        const config = JSON.parse(configStr)
                        const widthValue = config._width || 'full'
                        const widthPercent = {
                            'one_third': '33.33%',
                            'half': '50%',
                            'two_thirds': '66.66%',
                            'full': '100%'
                        }[widthValue] || '100%'
                        blockEl.style.flexBasis = widthPercent
                    } catch (e) {
                        // Fallback if config parsing fails
                    }
                }
            })
        }

        window.addEventListener('load', function () {
            applyBlockWidths()
            updateAllMoveButtons()
            postToParent({ type: 'ready' })
        })

        window.addEventListener('keydown', function (event) {
            const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0
            const cmdOrCtrl = isMac ? event.metaKey : event.ctrlKey

            if (cmdOrCtrl && !event.altKey) {
                if (
                    event.key === 'z' ||
                    event.key === 'Z' ||
                    event.key === 'y' ||
                    event.key === 'Y'
                ) {
                    postToParent({
                        type: 'keyboardShortcut',
                        key: event.key,
                        ctrlKey: event.ctrlKey,
                        metaKey: event.metaKey,
                        shiftKey: event.shiftKey,
                        altKey: event.altKey,
                    })
                    event.preventDefault()
                }
            }
        })

        function updateContent(blocks) {
            if (!Array.isArray(blocks)) {
                blocks = []
            }

            // Clear current content
            container.innerHTML = ''

            /*
             * Removing the last brick used to return early, before the clear
             * above — so the deleted brick stayed on the canvas and the drop
             * prompt never came back, leaving the band impossible to refill.
             */
            if (blocks.length === 0) {
                container.innerHTML = `<div class="mason-drop-zone mason-drop-zone--empty" data-drop-index="0" style="min-height: 4rem; align-items: center; justify-content: center; color: #9ca3af; position: absolute; inset: 0; margin: 0;">${placeholderText}</div>`
            } else {
                container.innerHTML = '<div class="mason-drop-zone" data-drop-index="0" style="min-height: 2rem"></div>'

                blocks.forEach((block, idx) => {
                    const brickId = block.attrs?.id || block.id || ''
                    const config = block.attrs?.config || block.config || {}
                    const preview = block.attrs?.preview || block.preview || ''

                    let decodedHtml = ''
                    if (preview) {
                        try {
                            decodedHtml = atob(preview)
                        } catch (e) {
                            decodedHtml = preview
                        }
                    }

                    const blockEl = document.createElement('div')
                    blockEl.className = 'mason-block'
                    blockEl.draggable = true
                    blockEl.setAttribute('data-block-index', idx.toString())
                    blockEl.setAttribute('data-brick-id', brickId)
                    blockEl.setAttribute('data-config', JSON.stringify(config))
                    blockEl.setAttribute('data-total-blocks', blocks.length.toString())

                    // Set width based on brick config
                    const widthValue = config._width || 'full'
                    const widthPercent = {
                        'one_third': '33.33%',
                        'half': '50%',
                        'two_thirds': '66.66%',
                        'full': '100%'
                    }[widthValue] || '100%'
                    blockEl.style.flexBasis = widthPercent

                    blockEl.innerHTML = `
                        <div class="mason-block-controls">
                            <button class="mason-block-btn" title="Move Up" data-action="move-up" data-block-index="${idx}" data-total-blocks="${blocks.length}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>
                            </button>
                            <button class="mason-block-btn" title="Move Down" data-action="move-down" data-block-index="${idx}" data-total-blocks="${blocks.length}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </button>
                            <button class="mason-block-btn" title="Add" data-action="add">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" /></svg>
                            </button>
                            <button class="mason-block-btn" title="Edit" data-action="edit">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" /></svg>
                            </button>
                            <button class="mason-block-btn" title="Delete" data-action="delete">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                        <div class="mason-block-content" style="overflow: auto; clear: both;">
                            ${decodedHtml}
                        </div>
                    `

                    container.appendChild(blockEl)

                    // Add drop zone after block
                    const dropZone = document.createElement('div')
                    dropZone.className = 'mason-drop-zone'
                    dropZone.setAttribute('data-drop-index', (idx + 1).toString())
                    dropZone.style.minHeight = '2rem'
                    container.appendChild(dropZone)
                })
            }

            postToParent({ type: 'contentUpdated' })
            setTimeout(updateAllMoveButtons, 100)
        }

        function shouldBeDisabled(data) {
            if (data.disabled !== undefined) {
                isDisabled = data.disabled
            }
            if (isDisabled) {
                container.style.pointerEvents = 'none'
            } else {
                container.style.pointerEvents = ''
            }
        }

        function insertBlock(brick, position) {
            postToParent({
                type: 'insertBlockRequest',
                brick,
                position,
            })
        }

        function updateBlock(index, brick) {
            postToParent({
                type: 'updateBlockRequest',
                index,
                brick,
            })
        }

        function deleteBlock(index) {
            postToParent({
                type: 'deleteBlockRequest',
                index,
            })
        }

        function moveBlock(from, to) {
            postToParent({
                type: 'moveBlockRequest',
                from,
                to,
            })
        }

        function selectBlock(index) {
            if (selectedBlock) {
                selectedBlock.classList.remove('selected')
            }

            const block = container.querySelector(
                `[data-block-index="${index}"]`,
            )
            if (block) {
                block.classList.add('selected')
                selectedBlock = block
                updateMoveButtons(block)
            }
        }

        function updateMoveButtons(block) {
            if (!block) return

            const index = parseInt(block.getAttribute('data-block-index'))
            const allBlocks = container.querySelectorAll('.mason-block')
            const totalBlocks = allBlocks.length

            const moveUpBtn = block.querySelector('[data-action="move-up"]')
            const moveDownBtn = block.querySelector('[data-action="move-down"]')

            if (moveUpBtn) {
                moveUpBtn.disabled = index === 0
            }

            if (moveDownBtn) {
                moveDownBtn.disabled = index === totalBlocks - 1
            }
        }

        function deselectAllBlocks() {
            container.querySelectorAll('.mason-block').forEach((block) => {
                block.classList.remove('selected')
            })
            selectedBlock = null
        }

        function updateAllMoveButtons() {
            const blocks = container.querySelectorAll('.mason-block')
            blocks.forEach((block, idx) => {
                block.setAttribute('data-block-index', idx.toString())
                updateMoveButtons(block)
            })
        }

        container.addEventListener('click', function (e) {
            const block = e.target.closest('.mason-block')
            if (!block) return

            const action = e.target.closest('[data-action]')
            if (action) {
                const actionType = action.getAttribute('data-action')
                const index = parseInt(block.getAttribute('data-block-index'))
                const brickId = block.getAttribute('data-brick-id')
                const config = JSON.parse(
                    block.getAttribute('data-config') || '{}',
                )

                if (actionType === 'edit') {
                    postToParent({
                        type: 'editBlock',
                        index,
                        brickId,
                        config,
                    })
                } else if (actionType === 'delete') {
                    postToParent({
                        type: 'deleteBlockRequest',
                        index,
                    })
                } else if (actionType === 'move-up') {
                    if (!action.disabled) {
                        postToParent({
                            type: 'moveBlockRequest',
                            from: index,
                            to: index - 1,
                        })
                    }
                } else if (actionType === 'move-down') {
                    if (!action.disabled) {
                        postToParent({
                            type: 'moveBlockRequest',
                            from: index,
                            to: index + 1,
                        })
                    }
                } else if (actionType === 'add') {
                    postToParent({
                        type: 'openBrickPicker',
                        blockIndex: index,
                    })
                }
            } else {
                const index = parseInt(block.getAttribute('data-block-index'))
                selectBlock(index)
            }
        })

        container.addEventListener('dblclick', function (e) {
            if (!dblClickToEdit) return

            const block = e.target.closest('.mason-block')
            if (!block) return

            if (e.target.closest('.mason-block-controls')) {
                return
            }

            if (e.target.closest('[data-action]')) {
                return
            }

            const index = parseInt(block.getAttribute('data-block-index'))
            const brickId = block.getAttribute('data-brick-id')
            const config = JSON.parse(block.getAttribute('data-config') || '{}')

            postToParent({
                type: 'editBlock',
                index,
                brickId,
                config,
            })
        })

        let draggedBlockIndex = null
        let draggedBlock = null
        let dragOverIndex = null
        let dragIdleTimer = null

        /*
         * Reveals the drop zones for the duration of a drag. A drag that
         * starts in the sidebar ends in the parent document, so there is no
         * dragend here to close it out — hence the idle timer alongside the
         * explicit clears on drop and dragend.
         */
        function markDragging() {
            document.body.classList.add('mason-dragging')
            clearTimeout(dragIdleTimer)
            dragIdleTimer = setTimeout(clearDragFeedback, 1200)
        }

        function clearDragFeedback() {
            clearTimeout(dragIdleTimer)
            document.body.classList.remove('mason-dragging')

            container
                .querySelectorAll('.mason-drop-zone.active')
                .forEach((zone) => {
                    zone.classList.remove('active')
                })
            container.querySelectorAll('.mason-block').forEach((block) => {
                block.style.outline = ''
                block.style.outlineOffset = ''
            })
        }

        container.addEventListener('dragstart', function (e) {
            const block = e.target.closest('.mason-block')
            if (!block) return

            if (e.target.closest('.mason-block-controls')) {
                e.preventDefault()
                return
            }

            if (
                !block.hasAttribute('draggable') ||
                block.getAttribute('draggable') !== 'true'
            ) {
                return
            }

            draggedBlock = block
            draggedBlockIndex = parseInt(block.getAttribute('data-block-index'))

            if (isNaN(draggedBlockIndex)) {
                return
            }

            block.classList.add('dragging')

            document.body.style.userSelect = 'none'
            document.body.style.webkitUserSelect = 'none'
            document.body.style.mozUserSelect = 'none'
            document.body.style.msUserSelect = 'none'

            const blockContent = block.querySelector('.mason-block-content')
            if (blockContent) {
                blockContent.style.pointerEvents = 'none'
                blockContent.style.userSelect = 'none'
                blockContent.style.webkitUserSelect = 'none'
                blockContent.style.mozUserSelect = 'none'
                blockContent.style.msUserSelect = 'none'
            }

            e.dataTransfer.effectAllowed = 'move'
            e.dataTransfer.setData('text/plain', draggedBlockIndex.toString())

            markDragging()
        })

        container.addEventListener('dragend', function (e) {
            document.body.style.userSelect = ''
            document.body.style.webkitUserSelect = ''
            document.body.style.mozUserSelect = ''
            document.body.style.msUserSelect = ''

            if (draggedBlock) {
                draggedBlock.classList.remove('dragging')
                const blockContent = draggedBlock.querySelector(
                    '.mason-block-content',
                )
                if (blockContent) {
                    blockContent.style.pointerEvents = ''
                    blockContent.style.userSelect = ''
                    blockContent.style.webkitUserSelect = ''
                    blockContent.style.mozUserSelect = ''
                    blockContent.style.msUserSelect = ''
                }
            }

            clearDragFeedback()

            draggedBlock = null
            draggedBlockIndex = null
            dragOverIndex = null
        })

        container.addEventListener('dragover', function (e) {
            e.preventDefault()

            // Also covers bricks dragged in from the sidebar, whose dragstart
            // fires in the parent document rather than here.
            markDragging()

            if (draggedBlockIndex !== null) {
                const dropZone = e.target.closest('.mason-drop-zone')
                const block = e.target.closest('.mason-block')

                if (dropZone) {
                    container
                        .querySelectorAll('.mason-drop-zone.active')
                        .forEach((zone) => {
                            zone.classList.remove('active')
                        })

                    const targetIndex = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )

                    if (
                        !isNaN(targetIndex) &&
                        targetIndex !== draggedBlockIndex &&
                        targetIndex !== draggedBlockIndex + 1
                    ) {
                        dropZone.classList.add('active')
                        dragOverIndex = targetIndex
                    }
                } else if (block) {
                    const blockIndex = parseInt(
                        block.getAttribute('data-block-index'),
                    )
                    if (blockIndex !== draggedBlockIndex) {
                        block.style.outline = '2px dashed #0ea5e9'
                        block.style.outlineOffset = '2px'
                    }
                }
            } else {
                const dropZone = e.target.closest('.mason-drop-zone')
                if (dropZone) {
                    dropZone.classList.add('active')
                    dragOverIndex = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )
                }
            }
        })

        container.addEventListener('dragleave', function (e) {
            const dropZone = e.target.closest('.mason-drop-zone')
            const block = e.target.closest('.mason-block')

            if (dropZone) {
                dropZone.classList.remove('active')
            }

            if (block && draggedBlockIndex !== null) {
                block.style.outline = ''
                block.style.outlineOffset = ''
            }
        })

        container.addEventListener('drop', function (e) {
            e.preventDefault()
            e.stopPropagation()

            clearDragFeedback()

            if (draggedBlockIndex !== null) {
                let dropZone = e.target.closest('.mason-drop-zone')
                if (
                    !dropZone &&
                    e.target.classList.contains('mason-drop-zone')
                ) {
                    dropZone = e.target
                }

                const block = e.target.closest('.mason-block')

                let targetIndex = null

                if (dropZone) {
                    const dropIndex = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )
                    if (!isNaN(dropIndex)) {
                        targetIndex = dropIndex
                    }
                } else if (block) {
                    const blockIndex = parseInt(
                        block.getAttribute('data-block-index'),
                    )
                    if (!isNaN(blockIndex)) {
                        targetIndex = blockIndex + 1
                    }
                }

                /*
                 * Both of these describe a drop that changes nothing: the
                 * slot the brick already occupies, and the slot immediately
                 * after it. The second one matters because the host component
                 * reserves to === from + 1 for the "move down" button and
                 * reads it as a swap, so sending it would shunt the brick down
                 * a place instead of leaving it put. Dropping a brick onto
                 * itself resolves to that same index.
                 */
                const isNoOpDrop =
                    targetIndex === draggedBlockIndex ||
                    targetIndex === draggedBlockIndex + 1

                if (targetIndex !== null && !isNaN(targetIndex) && !isNoOpDrop) {
                    const allBlocks = container.querySelectorAll('.mason-block')
                    const totalBlocks = allBlocks.length

                    if (targetIndex >= 0 && targetIndex <= totalBlocks) {
                        postToParent({
                            type: 'moveBlockRequest',
                            from: draggedBlockIndex,
                            to: targetIndex,
                        })
                    }
                }

                draggedBlockIndex = null
                draggedBlock = null
            } else {
                const dropZone = e.target.closest('.mason-drop-zone')
                if (dropZone) {
                    const position = parseInt(
                        dropZone.getAttribute('data-drop-index'),
                    )
                    const brickId = e.dataTransfer.getData('brick')

                    if (brickId && !isNaN(position)) {
                        postToParent({
                            type: 'insertBlockRequest',
                            brickId,
                            position,
                        })
                    }
                }
            }
        })
    })()
</script>
