class Select2Component extends W2.W2Component {
    jQueryElement
    #options
    id
    #selectionListeners

    constructor(options) {
        super()

        this.#selectionListeners = options.selectionListeners || []

        this.#options = options.select2 || {}
        this.jQueryElement = null

        let id = options.id
        if (!id) {
            id = W2.getID("select2_component", true)
        }
        this.id = id
    }

    render(container) {
        //clear old select
        if (this.jQueryElement) {
            this.jQueryElement.select2("destroy")
            this.jQueryElement.off("select2:select")
            this.jQueryElement.off("select2:clear")
        }

        //create new select
        const select = W2.html("select").id(this.id)

        //add it to the container before activating select2, as select2 needs a parent to work correctly
        container.content(
            select
        )

        this.jQueryElement = $(select.domNode)
        this.jQueryElement.select2(this.#options)

        this.jQueryElement.on("select2:select", (e) => {
            for (const selectionListener of this.#selectionListeners) {
                selectionListener(e.params.data)
            }
        })


        this.jQueryElement.on("select2:clear", (e) => {
            for (const selectionListener of this.#selectionListeners) {
                selectionListener(null)
            }
        })
    }
}

function select2Component(options) {
    const state = {
        jQueryElement: null
    }

    let id = options.id
    if (!id) {
        id = W2.getID("select2_component", true)
    }

    const selectionListeners = options.selectionListeners || []
    const closeListeners = options.closeListeners || []

    return W2.mount(state, (container, mount, state) => {
        const select = W2.html("select").id(id)
        container.content(select)

        state.jQueryElement = $(select.domNode)
        state.jQueryElement.select2(options.select2)

        state.jQueryElement.on("select2:select", (e) => {
            for (const selectionListener of selectionListeners) {
                selectionListener(e.params.data)
            }
        })

        state.jQueryElement.on("select2:clear", (e) => {
            for (const selectionListener of selectionListeners) {
                selectionListener(null)
            }
        })

        state.jQueryElement.on("select2:close", (e) => {
            for (const closeListener of closeListeners) {
                closeListener()
            }
        })

        if (options.open) {
            state.jQueryElement.select2('open');
        }
    })
}

function popupSelect2Component(options) {
    const state = {
        selectStage: false,
        selection:options.currentSelection || null,
        invalid: options.markInvalid || false
    }

    let injectListener = true

    options.open = true

    return W2.mount(state, (container, mount, state) => {

        //only do it on the first mount, but we need to do it in the mount
        if(injectListener){
            injectListener = false
            if(!options.selectionListeners) options.selectionListeners = []
            options.selectionListeners.push(
                (selection)=>{
                    state.selection = selection ? selection.text : null
                    state.selectStage = false
                    if(selection){
                        state.invalid = false
                    }
                    mount.update()
                }
            )
            if(!options.closeListeners) options.closeListeners = []
            options.closeListeners.push(
                ()=>{
                    state.selectStage = false
                    mount.update()
                }
            )
        }

        //select2 selector
        container.contentIf(state.selectStage,
            select2Component(options),
        )

        //change button
        .contentIf(!state.selectStage,
            W2.html("div")
                .class("input-group mb-3")
                .content(
                    W2.html("input")
                        .attribute("value", state.selection ? state.selection : "Nothing selected")
                        .attribute("type", "text")
                        .attribute("readonly", true)
                        .classIf(state.invalid, "is-invalid")
                        .class("form-control"),
                    W2.html("div")
                        .class("input-group-append")
                        .content(
                            W2.html("button")
                                .class("btn btn-secondary")
                                .content("Change")
                                .event("click", () => {
                                    state.selectStage = true
                                    //update ui to switch location selection stage
                                    mount.update()
                                })
                        )
                )
        )

    })
}