class BootstrapPopUp {
    builder
    name
    jQuery
    mount

    constructor(builder, title) {
        this.builder = builder
        this.title = title
        this.jQuery = null
        this.mount = null
    }

    static open(title, builder) {
        const popup = new BootstrapPopUp(builder, title)
        popup.open()
        return popup
    }

    open() {
        const content = W2.emptyHtml()
        this.builder(content, this)

        const modal = W2.html("div")
            .class("modal")
            .content(
                W2.html("div")
                    .class("modal-dialog modal-dialog-centered")
                    .content(
                        W2.html("div")
                            .class("modal-content")
                            //header
                            .content(
                                W2.html("div")
                                    .class("modal-header")
                                    .content(
                                        W2.html("h5")
                                            .class("modal-title")
                                            .content(this.title)
                                    )
                                    .content(
                                        W2.html("button")
                                            .class("close")
                                            .attribute("data-dismiss", "modal")
                                            .attribute("type", "button")
                                            .content(
                                                W2.html("span").content("×")
                                            )
                                    )
                            )
                            //body
                            .content(
                                W2.html("div")
                                    .class("modal-body")
                                    .content(content)
                            )
                    )
            )

        //mount it into the body
        this.mount = W2.mount((container) => {
            container.content(modal)
        })
        this.mount.addInto(document.body)

        this.jQuery = $(modal.domNode)
        this.jQuery.modal("show")
    }

    close() {
        if (this.jQuery) {
            this.jQuery.one("hidden.bs.modal", () => {
                this.mount.unmount()
            })

            this.jQuery.modal("hide")
        }
    }

    hide() {
        if (this.jQuery) {
            this.jQuery.modal("hide")
        }
    }

    reopen() {
        if (this.jQuery) {
            this.jQuery.modal("show")
        } else {
            this.open()
        }
    }
}

class BoostrapToast {
    static #containerDiv = null

    static open(title,content, time = 10) {
        if (typeof content === "function") {
            const container = W2.emptyHtml()
            content(container)
            content = container
        }

        // <button type="button" className="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
        //     <span aria-hidden="true">&times;</span>
        // </button>

        const toast = W2.html("div")
            .class("toast")
            .content(
                W2.html("div")
                    .class("toast-header")
                    .content(
                        W2.html("strong")
                            .class("mr-auto")
                            .content(title)
                    )
                    .content(
                        W2.html("button")
                            .attribute("type","button")
                            .class("close ml-2 mb-1 close")
                            .attribute("data-dismiss","toast")
                            .content("×")
                    )
            )
            .content(
                W2.html("div")
                    .class("toast-body")
                    .content(content)
            )

        const toastMount = W2.mount((container)=>{
            container.content(toast)
        })
        toastMount.addInto(this.#getContainerDiv())

        const jQuery = $(toast.domNode)
        jQuery.toast({
            delay: 1000*time
        })
        jQuery.toast("show")

        jQuery.one("hidden.bs.toast",()=>{
            toastMount.unmount()
        })
    }

    static #getContainerDiv() {
        if (this.#containerDiv) return this.#containerDiv

        this.#containerDiv = W2.html("div")
            .style("position", "fixed")
            .style("min-width", "10rem")
            .style("top", "4rem")
            .style("right", "1rem")


        return this.#containerDiv.addInto(document.body)
    }
}

function tooltipComponent(base,tooltip) {
    //wrap it in a container so it has a parent
    const container = W2.emptyHtml().content(base)

    base.attribute("data-placement","top")
        .attribute("title",tooltip)

    const jQuery = $(base.domNode)
    jQuery.tooltip("show")

    return container
}