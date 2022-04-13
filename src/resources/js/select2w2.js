class Select2Component extends W2.W2Component{
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
        if(!id){
            id = W2.getID("select2_component",true)
        }
        this.id = id
    }

    render(container) {
        //clear old select
        if(this.jQueryElement) {
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

        this.jQueryElement.on("select2:select",(e)=>{
            for (const selectionListener of this.#selectionListeners) {
                selectionListener(e.params.data)
            }
        })


        this.jQueryElement.on("select2:clear",(e)=>{
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
    if(!id){
        id = W2.getID("select2_component",true)
    }

    const selectionListeners = options.selectionListeners || []

    return W2.mount(state,(container,mount,state)=>{
        const select = W2.html("select").id(id)
        container.content(select)

        state.jQueryElement = $(select.domNode)
        state.jQueryElement.select2(options.select2)

        state.jQueryElement.on("select2:select",(e)=>{
            for (const selectionListener of selectionListeners) {
                selectionListener(e.params.data)
            }
        })

        state.jQueryElement.on("select2:clear",(e)=>{
            for (const selectionListener of selectionListeners) {
                selectionListener(null)
            }
        })
    })
}