<style scoped>

</style>

<template>
    <div id="item_x">

        <span>
            <input type="checkbox" id="checkbox" v-model="checked" v-on:change"itemChecked">
        </span>

        <span>
            {{ task.title }}
        </span>

    </div>
</template>


<script>

export default {
    components: {},
    mixins: [],
    data () {
      return {
        csrf: '',
        checked: ''
      }
    },
    mounted() {
        this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
    },
    props: [
        'task',
        'taskList'
    ],
    computed: {},
    methods: {
        itemChecked(){
            //
            console.log('Check box activated!');
        }
    }
};

</script>