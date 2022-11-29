<script setup>
    import { useChat } from '~/store/chat'

    const store = useChat()

    const content = ref('Aqui vem o conteúdo')

    const send = () => {
        if (content.value.length === 0)
            return

        alert(`Enviando: ${content.value}`)
        content.value = ''
    }
</script>

<template>
    <div class="input-group mb-0">
        <div
            class="input-group-prepend"
            @click="send"
        >
            <span class="input-group-text">
                <i class="fa fa-send" />
            </span>
        </div>

        <input
            v-model="content"
            type="text"
            class="form-control"
            placeholder="Insira o texto aqui..."
            @keydown.enter="send"
        >
    </div>
</template>
