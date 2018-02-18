<style scoped>

</style>

<template>

    <div>
        <datepicker :value="currentDate" v-on:selected="dateChosen" :inline="true"></datepicker>
        <p> Some text goes here in my calendar widget.</p>
    </div>

</template>


<script>
    import Datepicker from 'vuejs-datepicker';
    export default {
        components: {
            Datepicker
        },
        mixins: [],
        data () {
            return {
                csrf: '',
                calendarId: '',
                calendar: '',
                currentDate: ''
            }
        },
        mounted() {
            this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
            // this.fetchCalendar();
            this.currentDate = new Date();

        },
        props: [
            'aspectId',
            'aspectType',
            'aspectNotes',
            'aspectData',
            'aspectSource',
            'hidden',
            'subjectId',
            'settingsCalendarId',
            'title'
        ],
        computed: {
            doUpdate: function(){
                if (typeof this.aspectId === 'undefined' || this.aspectId === null) {
                    return false;
                } else {
                    return true;
                }
            },
            actionPath: function(){
                if ( this.doUpdate ){
                    var action_path = '/aspect/' + this.aspectId + '/edit';
                    return action_path;
                } else {
                    return '/aspect/create';
                }
            }
        },
        methods: {
            fetchCalendar(){
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
            dateChosen(){

            }
        }
    };

</script>