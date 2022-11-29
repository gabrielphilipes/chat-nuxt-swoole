<script setup>
    const props = defineProps({
        receive: { type: Boolean, default: false },
    })
</script>

<template>
    <li class="clearfix">
        <div
            class="message-data"
            :class="{ 'text-right': !props.receive }"
        >
            <span class="message-data-time">10:10 AM, Today</span>
        </div>

        <div
            class="message other-message"
            :class="{ 'float-right': !props.receive }"
        >
            Hi Aiden, how are you? How is the project coming along?
        </div>
    </li>
</template>

<style scoped>

</style>
