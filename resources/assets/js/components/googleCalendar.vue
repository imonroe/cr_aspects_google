<style scoped>

    .centered {
        margin: auto;
    }

</style>

<template>

    <div >
        <datepicker class="centered" :value="currentDate" v-on:selected="dateChosen" :inline="true"></datepicker>
        
        <p> Some text goes here in my calendar widget.</p>

        <div>
            <p v-for="event in calendar.data.items" :key="event.id"> {{ event.summary }} - {{ formatDate(event.start) }} </p>
        </div>

    </div>

</template>


<script>
    import Datepicker from 'vuejs-datepicker';
    import moment from 'moment';
    export default {
        components: {
            Datepicker
        },
        mixins: [],
        data () {
            return {
                csrf: '',
                calendarId: '',
                calendar: {"data": {"items": []} },
                currentDate: '',
                startDate: '',
                endDate: ''
            }
        },
        created() {
            this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
            this.setCalendar();
            this.fetchCalendar();
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

        },
        methods: {
            setCalendar(){
                this.calendarId = this.settingsCalendarId;
                this.currentDate = new Date();
                this.startDate = new Date();
                this.startDate.setHours(0);
                this.startDate.setMinutes(0);
                this.startDate.setSeconds(1);
                this.endDate = new Date();
                this.endDate.setHours(23);
                this.endDate.setMinutes(59);
                this.endDate.setSeconds(59);
            },
            formatDate(googleDate){
                // This is a weird thing, because google uses strange date objects in their JSON.
                var dateObject;
                if ( googleDate.dateTime !=null ){
                    dateObject = new Date(googleDate.dateTime);
                } else {
                    dateObject = new Date(googleDate.date);
                }
                return moment(dateObject).format('llll');
            },
            fetchCalendar(){
                var self=this;
                var fd = new Object();
                fd._token = this.csfr;
                fd.start_date = this.startDate;
                fd.end_date = this.endDate;
                fd.calendar_id = this.calendarId;
                //var fd_string = JSON.stringify(fd);
                var data = new Object();
                data.params = fd;
                axios.get('/gcal/calendar', data)
                    .then(function(response){
                        self.calendar = response;
                        console.log(response);
                    })
                    .catch(function(error){
                        console.log(error);
                    });
            },
            createCalendar(){

            },
            dateChosen(date_object){
                console.log(date_object);
                this.startDate = new Date(date_object);
                this.startDate.setHours(0);
                this.startDate.setMinutes(0);
                this.startDate.setSeconds(1);
                this.endDate = new Date( this.startDate.toDateString() );
                this.endDate.setHours(23);
                this.endDate.setMinutes(59);
                this.endDate.setSeconds(59);

                this.fetchCalendar();
            }
        }
    };

</script>