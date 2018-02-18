<style scoped>

</style>



<template>

    <div>

        <div>
            Available calendars
            <ul>
                <li v-for="calendar in calendarList.data.items" :key="calendar.id"> {{ calendar.summary }} </li>
            </ul>
        </div>

        <div>
            select a calendar
        </div>

    </div>

</template>



<script>
    export default {
        components: {},
        mixins: [],
        data () {
            return {
                csrf: '',
                calendarList: ''
            }
        },
        mounted() {
            this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
            this.fetchCalendars();
        },
        props: [
            'aspectId',
            'aspectType',
            'aspectNotes',
            'aspectData',
            'aspectSource',
            'hidden',
            'subjectId',
            'settingsListId',
            'title'
        ],
        computed: {},
        methods: {
            fetchCalendars(){
                var self=this;
                axios.get('/gcal/available_calendars')
                    .done(function(response){
                        self.calendarList = response;
                        console.log(response);
                    })
                    .error(function(error){
                        console.log(error);
                    });

            },
            createCalendar(){

            },
            createAspect(){

            }

        }
    };

</script>